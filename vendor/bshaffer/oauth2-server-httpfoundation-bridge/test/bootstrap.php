<?php

if (file_exists($autoload_file = __DIR__.'/../vendor/autoload.php')) {
    require_once $autoload_file;
}

// Allows us to test across multiple versions of PHPUnit
if (!class_exists('\PHPUnit\Framework\TestCase', true)) {
    class_alias(
        '\PHPUnit_Framework_TestCase',
        '\PHPUnit\Framework\TestCase'
    );
} elseif (!class_exists('\PHPUnit_Framework_TestCase', true)) {
    class_alias(
        '\PHPUnit\Framework\TestCase',
        '\PHPUnit_Framework_TestCase'
    );
}
