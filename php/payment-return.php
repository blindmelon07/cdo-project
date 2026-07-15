<?php
session_start();
require_once __DIR__ . '/paymongo.php';

$redirectBase = '../pmes.html';
$applicant = $_SESSION['gsac_applicant'] ?? null;
$intentId = $_GET['payment_intent_id'] ?? ($applicant['payment_intent_id'] ?? null);

if (!$intentId) {
    header('Location: ' . $redirectBase . '?paid=failed&reason=missing_payment');
    exit;
}

try {
    // Always re-check the status with PayMongo directly — never trust query params alone.
    $intent = paymongo_request('GET', "/payment_intents/{$intentId}");
    $status = $intent['data']['attributes']['status'] ?? '';

    if ($status === 'succeeded') {
        $ref  = 'GSAC-' . date('Y') . '-' . strtoupper(substr(md5($intentId), 0, 5));
        $name = $applicant ? trim($applicant['firstName'] . ' ' . $applicant['lastName']) : '';
        unset($_SESSION['gsac_applicant']);

        header('Location: ' . $redirectBase
            . '?paid=success&ref=' . urlencode($ref)
            . '&name=' . urlencode($name));
        exit;
    }

    header('Location: ' . $redirectBase . '?paid=failed&reason=' . urlencode($status ?: 'unknown'));
    exit;
} catch (Exception $e) {
    header('Location: ' . $redirectBase . '?paid=failed&reason=' . urlencode($e->getMessage()));
    exit;
}
