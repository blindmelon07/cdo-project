<?php
// ============================================================
// PayMongo webhook receiver.
// Registered in the PayMongo Dashboard as: https://<your-domain>/php/webhook.php
//
// This is the reliable, server-to-server confirmation channel: unlike
// payment-return.php (which depends on the customer's browser making it
// back to your site), PayMongo calls this endpoint directly whenever a
// payment's status changes, so it works even if the customer closes the
// tab right after paying.
// ============================================================

require_once __DIR__ . '/paymongo.php';

$rawBody = file_get_contents('php://input');
$signatureHeader = $_SERVER['HTTP_PAYMONGO_SIGNATURE'] ?? '';

if ($signatureHeader === '') {
    http_response_code(400);
    exit('Missing signature header');
}

// Header format: t=<timestamp>,te=<test_mode_signature>,li=<live_mode_signature>
$parts = [];
foreach (explode(',', $signatureHeader) as $pair) {
    [$key, $value] = array_pad(explode('=', $pair, 2), 2, null);
    if ($key !== null) {
        $parts[$key] = $value;
    }
}

$timestamp = $parts['t'] ?? null;
// We're on live keys (sk_live_...), so verify against the live-mode signature.
$signature = $parts['li'] ?? null;

if (!$timestamp || !$signature) {
    http_response_code(400);
    exit('Malformed signature header');
}

$signedPayload = $timestamp . '.' . $rawBody;
$expectedSignature = hash_hmac('sha256', $signedPayload, PAYMONGO_WEBHOOK_SECRET);

if (!hash_equals($expectedSignature, $signature)) {
    http_response_code(400);
    exit('Invalid signature');
}

$event = json_decode($rawBody, true);
$eventType = $event['data']['attributes']['type'] ?? '';
$resource  = $event['data']['attributes']['data'] ?? [];
$resourceAttrs = $resource['attributes'] ?? [];

if ($eventType === 'payment.paid' || $eventType === 'payment.failed') {
    $intentId = $resourceAttrs['payment_intent_id'] ?? null;
    $record = [
        'received_at' => date('c'),
        'event' => $eventType,
        'payment_intent_id' => $intentId,
        'reference' => $intentId ? gsac_reference_from_intent($intentId) : null,
        'amount' => $resourceAttrs['amount'] ?? null,
        'method' => $resourceAttrs['source']['type'] ?? ($resourceAttrs['payment_method_used'] ?? null),
        'billing_name' => $resourceAttrs['billing']['name'] ?? null,
        'billing_email' => $resourceAttrs['billing']['email'] ?? null,
    ];
    file_put_contents(
        __DIR__ . '/data/payments.log',
        json_encode($record) . "\n",
        FILE_APPEND | LOCK_EX
    );
}

// Any other event type is simply ignored — PayMongo only requires a 2xx response.
http_response_code(200);
echo 'ok';
