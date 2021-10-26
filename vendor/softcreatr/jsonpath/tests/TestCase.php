<?php

/**
 * JSONPath implementation for PHP.
 *
 * @license https://github.com/SoftCreatR/JSONPath/blob/main/LICENSE  MIT License
 */

namespace Flow\JSONPath\Test;

use BadMethodCallException;
use PHPUnit\Framework\Exception as PHPUnit_Framework_Exception;
use PHPUnit\Framework\TestCase as PHPUnit_Framework_TestCase;

/**
 * Base test case for JSONPath test-suite, basically a shim
 * for older/newer phpunit versions.
 *
 * @coversNothing
 */
class TestCase extends PHPUnit_Framework_TestCase
{
    /**
     * @var null|string
     */
    private $expectedException;

    /**
     * @var null|string
     */
    private $expectedExceptionMessage;

    /**
     * @param string $needle
     * @param string $haystack
     * @param string $message
     *
     * @return void
     */
    public static function assertEqualsCanonicalizing($needle, $haystack, $message = '')
    {
        if (is_callable('parent::' . __FUNCTION__)) {
            /** @noinspection PhpUndefinedMethodInspection */
            parent::assertEqualsCanonicalizing($needle, $haystack, $message);

            return;
        }

        /** @noinspection PhpUnitDeprecationsInspection */
        self::assertEquals($needle, $haystack, "\$canonicalize = true", 0.0, 10, true);
    }

    /**
     * @param string $exception
     *
     * @return void
     */
    public function expectException($exception)
    {
        if (is_callable('parent::' . __FUNCTION__)) {
            parent::expectException($exception);

            return;
        }

        $this->expectedException = $exception;
        $this->setExpectedException($exception);
    }

    /**
     * @param string $class
     * @param null|string $message
     * @param null|int|string $code
     *
     * @return void
     */
    public function setExpectedException($class, $message = null, $code = null)
    {
        if (is_callable('parent::' . __FUNCTION__)) {
            parent::setExpectedException($class, $message, $code);
            return;
        }
        if (is_callable('parent::expectException')) {
            $this->expectException($class);
        }
        if (null !== $message && is_callable('parent::expectExceptionMessage')) {
            $this->expectExceptionMessage($message);
        }
        if (null !== $code && is_callable('parent::expectExceptionCode')) {
            $this->expectExceptionCode($code);
        }
    }

    /**
     * @param string $message
     *
     * @return void
     * @throws PHPUnit_Framework_Exception
     *
     */
    public function expectExceptionMessage($message)
    {
        if (is_callable('parent::' . __FUNCTION__)) {
            parent::expectExceptionMessage($message);

            return;
        }

        if (null === $this->expectedException) {
            throw new BadMethodCallException('Hmm this is message without class *gg* - reflection?');
        }

        $this->expectedExceptionMessage = $message;
        $this->setExpectedException($this->expectedException, $message);
    }

    /**
     * @param int|string $code
     *
     * @return void
     * @throws PHPUnit_Framework_Exception
     *
     */
    public function expectExceptionCode($code)
    {
        if (is_callable('parent::' . __FUNCTION__)) {
            parent::expectExceptionCode($code);

            return;
        }

        if (null === $this->expectedException) {
            throw new BadMethodCallException('No exception expected');
        }

        $this->setExpectedException($this->expectedException, $this->expectedExceptionMessage, $code);
    }
}
