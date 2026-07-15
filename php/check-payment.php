<?php
// Polled by the QR Ph flow on pmes.html while the customer scans the code —
// GCash/Maya don't need this since they redirect back via return_url instead.
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/paymongo.php';

$intentId = $_GET['payment_intent_id'] ?? ($_SESSION['gsac_applicant']['payment_intent_id'] ?? null);

if (!$intentId) {
    http_response_code(400);
    echo json_encode(['error' => 'No active payment to check.']);
    exit;
}

try {
    $intent = paymongo_request('GET', "/payment_intents/{$intentId}");
    echo json_encode(['status' => $intent['data']['attributes']['status'] ?? 'unknown']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
