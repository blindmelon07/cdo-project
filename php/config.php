<?php
// ============================================================
// PayMongo API credentials
// Get these from https://dashboard.paymongo.com/developers
// (Developers -> API Keys). Start with the TEST keys (sk_test_...
// pk_test_...) so you can try the flow with PayMongo's test
// GCash/Maya/QR Ph accounts before going live.
//
// IMPORTANT: PAYMONGO_SECRET_KEY must stay server-side only.
// Never paste it into pmes.html, main.js, or any file served
// to the browser.
// ============================================================

define('PAYMONGO_SECRET_KEY', 'sk_test_REPLACE_ME');
define('PAYMONGO_PUBLIC_KEY', 'pk_test_REPLACE_ME');

// Full base URL of the site, no trailing slash.
// Update this once you know your Hostinger temporary domain,
// e.g. 'https://yourname.hostingersite.com'
define('SITE_URL', 'http://localhost');
