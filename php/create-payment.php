<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/paymongo.php';

// The GSAC membership fee is fixed — never trust an amount from the client.
const MEMBERSHIP_FEE_CENTAVOS = 1000; // PHP 10.00

$allowedMethods = ['gcash', 'paymaya', 'qrph'];

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$method = $input['method'] ?? '';

if (!in_array($method, $allowedMethods, true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Unsupported payment method.']);
    exit;
}

$applicant  = $input['applicant'] ?? [];
$firstName  = trim($applicant['firstName'] ?? '');
$lastName   = trim($applicant['lastName'] ?? '');
$email      = trim($applicant['email'] ?? '');
$mobile     = trim($applicant['mobile'] ?? '');

if ($firstName === '' || $lastName === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Please fill in your name before paying.']);
    exit;
}

try {
    // 1. Create a Payment Intent for the fixed membership fee.
    $intent = paymongo_request('POST', '/payment_intents', [
        'data' => [
            'attributes' => [
                'amount' => MEMBERSHIP_FEE_CENTAVOS,
                'currency' => 'PHP',
                'payment_method_allowed' => [$method],
                'capture_type' => 'automatic',
                'description' => 'GSAC Membership Fee - ' . $firstName . ' ' . $lastName,
            ],
        ],
    ]);
    $intentId  = $intent['data']['id'];
    $clientKey = $intent['data']['attributes']['client_key'];

    // 2. Create a Payment Method for the chosen e-wallet / QR Ph (no card data needed).
    $paymentMethod = paymongo_request('POST', '/payment_methods', [
        'data' => [
            'attributes' => [
                'type' => $method,
                'billing' => [
                    'name'  => $firstName . ' ' . $lastName,
                    'email' => $email !== '' ? $email : null,
                    'phone' => $mobile !== '' ? $mobile : null,
                ],
            ],
        ],
    ]);
    $paymentMethodId = $paymentMethod['data']['id'];

    // Remember the applicant + intent for when PayMongo redirects the user back.
    $_SESSION['gsac_applicant'] = [
        'firstName' => $firstName,
        'lastName'  => $lastName,
        'email'     => $email,
        'mobile'    => $mobile,
        'payment_intent_id' => $intentId,
    ];

    // 3. Attach the Payment Method to the Payment Intent and get the redirect URL.
    $returnUrl = rtrim(SITE_URL, '/') . '/php/payment-return.php';
    $attach = paymongo_request('POST', "/payment_intents/{$intentId}/attach", [
        'data' => [
            'attributes' => [
                'payment_method' => $paymentMethodId,
                'client_key'     => $clientKey,
                'return_url'     => $returnUrl,
            ],
        ],
    ]);

    $nextAction  = $attach['data']['attributes']['next_action'] ?? null;
    $redirectUrl = $nextAction['redirect']['url'] ?? null;

    if ($redirectUrl) {
        echo json_encode(['redirect_url' => $redirectUrl]);
        exit;
    }

    // Rare: some test-mode flows resolve without a redirect step.
    $status = $attach['data']['attributes']['status'] ?? '';
    if ($status === 'succeeded') {
        echo json_encode(['redirect_url' => $returnUrl . '?payment_intent_id=' . $intentId]);
        exit;
    }

    // Temporary diagnostics: surface PayMongo's actual response shape so we can
    // see why no redirect URL came back, instead of a generic message.
    throw new Exception('PayMongo did not return a checkout URL. status=' . $status
        . ' next_action=' . json_encode($nextAction)
        . ' last_payment_error=' . json_encode($attach['data']['attributes']['last_payment_error'] ?? null));
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
