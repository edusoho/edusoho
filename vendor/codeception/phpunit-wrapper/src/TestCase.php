<?php

namespace Codeception\PHPUnit;


use PHPUnit\Framework\AssertionFailedError;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{

    protected function setUp()
    {
        if (method_exists($this, '_setUp')) {
            $this->_setUp();
        }
    }

    protected function tearDown()
    {
        if (method_exists($this, '_tearDown')) {
            $this->_tearDown();
        }
    }

    public static function setUpBeforeClass()
    {
        if (method_exists(get_called_class(), '_setUpBeforeClass')) {
            static::_setUpBeforeClass();
        }
    }

    public static function tearDownAfterClass()
    {
        if (method_exists(get_called_class(), '_tearDownAfterClass')) {
            static::_tearDownAfterClass();
        }
    }

    public static function assertStringContainsString($needle, $haystack, $message = '')
    {
        if (!is_string($needle)) {
            throw new AssertionFailedError('Needle is not string');
        }
        if (!is_string($haystack)) {
            throw new AssertionFailedError('Haystack is not string');
        }
        \Codeception\PHPUnit\TestCase::assertContains($needle, $haystack, $message);
    }

    public static function assertStringNotContainsString($needle, $haystack, $message = '')
    {

        if (!is_string($needle)) {
            throw new AssertionFailedError('Needle is not string');
        }
        if (!is_string($haystack)) {
            throw new AssertionFailedError('Haystack is not string');
        }
        \Codeception\PHPUnit\TestCase::assertNotContains($needle, $haystack, $message);
    }

    public static function assertStringContainsStringIgnoringCase($needle, $haystack, $message = '')
    {
        if (!is_string($needle)) {
            throw new AssertionFailedError('Needle is not string');
        }
        if (!is_string($haystack)) {
            throw new AssertionFailedError('Haystack is not string');
        }
        \Codeception\PHPUnit\TestCase::assertContains($needle, $haystack, $message, true);
    }

    public static function assertStringNotContainsStringIgnoringCase($needle, $haystack, $message = '')
    {

        if (!is_string($needle)) {
            throw new AssertionFailedError('Needle is not string');
        }
        if (!is_string($haystack)) {
            throw new AssertionFailedError('Haystack is not string');
        }
        \Codeception\PHPUnit\TestCase::assertNotContains($needle, $haystack, $message, true);
    }

    public static function assertIsArray($actual, $message = '')
    {
        \Codeception\PHPUnit\TestCase::assertInternalType('array', $actual, $message);
    }

    public static function assertIsBool($actual, $message = '')
    {
        \Codeception\PHPUnit\TestCase::assertInternalType('bool', $actual, $message);
    }

    public static function assertIsFloat($actual, $message = '')
    {
        \Codeception\PHPUnit\TestCase::assertInternalType('float', $actual, $message);
    }

    public static function assertIsInt($actual, $message = '')
    {
        \Codeception\PHPUnit\TestCase::assertInternalType('int', $actual, $message);
    }

    public static function assertIsNumeric($actual, $message = '')
    {
        \Codeception\PHPUnit\TestCase::assertInternalType('numeric', $actual, $message);
    }

    public static function assertIsObject($actual, $message = '')
    {
        \Codeception\PHPUnit\TestCase::assertInternalType('object', $actual, $message);
    }

    public static function assertIsResource($actual, $message = '')
    {
        \Codeception\PHPUnit\TestCase::assertInternalType('resource', $actual, $message);
    }

    public static function assertIsString($actual, $message = '')
    {
        \Codeception\PHPUnit\TestCase::assertInternalType('string', $actual, $message);
    }

    public static function assertIsScalar($actual, $message = '')
    {
        \Codeception\PHPUnit\TestCase::assertInternalType('scalar', $actual, $message);
    }

    public static function assertIsCallable($actual, $message = '')
    {
        \Codeception\PHPUnit\TestCase::assertInternalType('callable', $actual, $message);
    }

    public static function assertIsNotArray($actual, $message = '')
    {
        \Codeception\PHPUnit\TestCase::assertNotInternalType('array', $actual, $message);
    }

    public static function assertIsNotBool($actual, $message = '')
    {
        \Codeception\PHPUnit\TestCase::assertNotInternalType('bool', $actual, $message);
    }

    public static function assertIsNotFloat($actual, $message = '')
    {
        \Codeception\PHPUnit\TestCase::assertNotInternalType('float', $actual, $message);
    }

    public static function assertIsNotInt($actual, $message = '')
    {
        \Codeception\PHPUnit\TestCase::assertNotInternalType('int', $actual, $message);
    }

    public static function assertIsNotNumeric($actual, $message = '')
    {
        \Codeception\PHPUnit\TestCase::assertNotInternalType('numeric', $actual, $message);
    }

    public static function assertIsNotObject($actual, $message = '')
    {
        \Codeception\PHPUnit\TestCase::assertNotInternalType('object', $actual, $message);
    }

    public static function assertIsNotResource($actual, $message = '')
    {
        \Codeception\PHPUnit\TestCase::assertNotInternalType('resource', $actual, $message);
    }

    public static function assertIsNotString($actual, $message = '')
    {
        \Codeception\PHPUnit\TestCase::assertNotInternalType('string', $actual, $message);
    }

    public static function assertIsNotScalar($actual, $message = '')
    {
        \Codeception\PHPUnit\TestCase::assertNotInternalType('scalar', $actual, $message);
    }

    public static function assertIsNotCallable($actual, $message = '')
    {
        \Codeception\PHPUnit\TestCase::assertNotInternalType('callable', $actual, $message);
    }

    public static function assertIsNotIterable($actual, $message = '')
    {
        \Codeception\PHPUnit\TestCase::assertNotInternalType('iterable', $actual, $message);
    }

    public static function assertEqualsCanonicalizing($expected, $actual, $message = '')
    {
        \Codeception\PHPUnit\TestCase::assertEquals($expected, $actual, $message, 0.0, 10, true, false);
    }

    public static function assertNotEqualsCanonicalizing($expected, $actual, $message = '')
    {
        \Codeception\PHPUnit\TestCase::assertNotEquals($expected, $actual, $message, 0.0, 10, true, false);
    }

    public static function assertEqualsIgnoringCase($expected, $actual, $message = '')
    {
        \Codeception\PHPUnit\TestCase::assertEquals($expected, $actual, $message, 0.0, 10, false, true);
    }

    public static function assertNotEqualsIgnoringCase($expected, $actual, $message = '')
    {
        \Codeception\PHPUnit\TestCase::assertNotEquals($expected, $actual, $message, 0.0, 10, false, true);
    }

    public static function assertEqualsWithDelta($expected, $actual, $delta, $message = '')
    {
        \Codeception\PHPUnit\TestCase::assertEquals($expected, $actual, $message, $delta, 10, false, false);
    }

    public static function assertNotEqualsWithDelta($expected, $actual, $delta, $message = '')
    {
        \Codeception\PHPUnit\TestCase::assertNotEquals($expected, $actual, $message, $delta, 10, false, false);
    }
}
