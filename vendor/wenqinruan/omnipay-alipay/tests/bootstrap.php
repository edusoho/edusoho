<?php

error_reporting(E_ALL | E_STRICT);

// include the composer autoloader
$autoloader = require __DIR__ . '/../vendor/autoload.php';

// autoload abstract TestCase classes in test directory
$autoloader->add('Omnipay', __DIR__);

define('ALIPAY_ASSET_DIR', realpath(__DIR__ . '/Assets'));

$configFile = realpath(__DIR__ . '/../config.php');

if (file_exists($configFile) && false) {
    include_once $configFile;
} else {
    define('ALIPAY_PARTNER', '2088011436420182');
    define('ALIPAY_KEY', '18x8lAi0a1520st1hvxcnt7m4w1whkbs');
    define('ALIPAY_SELLER_ID', '2088011436420182');
    define('ALIPAY_PUBLIC_KEY', ALIPAY_ASSET_DIR . '/alipay_public_key.pem');
    define('ALIPAY_LEGACY_PRIVATE_KEY', ALIPAY_ASSET_DIR . '/dist/legacy/rsa_private_key.pem');
    define('ALIPAY_LEGACY_PUBLIC_KEY', ALIPAY_ASSET_DIR . '/dist/legacy/alipay_public_key.pem');
    define('ALIPAY_AOP_PUBLIC_KEY', ALIPAY_ASSET_DIR . '/dist/aop/alipay_public_key.pem');
    define('ALIPAY_AOP_PRIVATE_KEY', ALIPAY_ASSET_DIR . '/dist/aop/rsa_private_key.pem');

    define('ALIPAY_APP_ID', '2088011436421111');
    define('ALIPAY_APP_PRIVATE_KEY', ALIPAY_ASSET_DIR . '/dist/aop/rsa_private_key.pem');
    define('ALIPAY_APP_ENCRYPT_KEY', 'aGVsbG93b3JsZGhleWhleWhleQ==');
}

if (! function_exists('dd')) {
    function dd()
    {
        foreach (func_get_args() as $arg) {
            var_dump($arg);
        }
        exit(0);
    }
}
