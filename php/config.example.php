<?php
// ============================================================
// PayMongo API credentials — TEMPLATE
//
// Copy this file to config.php (which is gitignored) and fill
// in your real values. Get keys from:
// https://dashboard.paymongo.com/developers (Developers -> API Keys)
//
// Start with the TEST keys (sk_test_... / pk_test_...) so you can
// try the flow with PayMongo's test GCash/Maya/QR Ph accounts
// before switching to live keys.
//
// IMPORTANT: PAYMONGO_SECRET_KEY and PAYMONGO_WEBHOOK_SECRET must
// stay server-side only, and config.php must never be committed
// to git — that's why it's listed in .gitignore.
// ============================================================

define('PAYMONGO_SECRET_KEY', 'sk_test_REPLACE_ME');
define('PAYMONGO_PUBLIC_KEY', 'pk_test_REPLACE_ME');

// Webhook signing secret — shown once in the PayMongo Dashboard right after
// you create the webhook (Developers -> Webhooks -> click the webhook -> "Signing Secret").
// Used by php/webhook.php to verify that incoming requests really came from PayMongo.
define('PAYMONGO_WEBHOOK_SECRET', 'whsec_REPLACE_ME');

// Full base URL of the site, no trailing slash.
// e.g. 'https://yourname.hostingersite.com'
define('SITE_URL', 'http://localhost');
