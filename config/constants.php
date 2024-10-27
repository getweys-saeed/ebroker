<?php

// Ensure the constant is defined only once
if (!defined('PERMISSION_ERROR_MSG')) {
    define('PERMISSION_ERROR_MSG', 'You are not authorized to operate on the module ');
}

if (!defined('JWT_SECRET_KEY')) {
    define('JWT_SECRET_KEY', '404D635166546A576E5A7234753778214125442A472D4B614E645267556B5870');
}

if (!defined('PAYPAL_SANDBOX_MODE')) {
    define('PAYPAL_SANDBOX_MODE', true);
}

if (!defined('sandbox')) {
    define('sandbox', true);
}

// Define business only if it hasn't been defined yet
if (!defined('business')) {
    define('business', 'sb-uefcv23946367@business.example.com');
}

// Uncomment these for Live settings if needed
// if (!defined('PAYPAL_LIVE_BUSINESS_EMAIL')) {
//     define('PAYPAL_LIVE_BUSINESS_EMAIL', '');
// }

// if (!defined('PAYPAL_CURRENCY')) {
//     define('PAYPAL_CURRENCY', 'USD');
// }

return [
    'CACHE' => [
        'SYSTEM' => [
            'DEFAULT_LANGUAGE' => 'default_language',
            'SETTINGS' => 'systemSettings'
        ],
    ]
];
