<?php
require_once __DIR__ . '/config.php';

/**
 * Minimal wrapper around the PayMongo REST API using cURL + Basic Auth.
 * Throws on any transport error or PayMongo-reported error (status >= 400).
 */
function paymongo_request($method, $path, $body = null) {
    $ch = curl_init('https://api.paymongo.com/v1' . $path);

    $headers = [
        'Content-Type: application/json',
        'Authorization: Basic ' . base64_encode(PAYMONGO_SECRET_KEY . ':'),
    ];

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }

    $response = curl_exec($ch);
    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new Exception('PayMongo request failed: ' . $error);
    }

    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = json_decode($response, true);
    if ($status >= 400) {
        $message = $data['errors'][0]['detail'] ?? ('PayMongo error (HTTP ' . $status . ')');
        throw new Exception($message);
    }

    return $data;
}

/**
 * Deterministic, human-facing reference number derived from a Payment Intent id.
 * Used by both payment-return.php (shown to the applicant) and webhook.php
 * (logged server-side) so the two always agree on the same reference.
 */
function gsac_reference_from_intent($intentId) {
    return 'GSAC-' . date('Y') . '-' . strtoupper(substr(md5($intentId), 0, 5));
}
