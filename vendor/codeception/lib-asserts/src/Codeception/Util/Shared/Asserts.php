<?php
namespace Codeception\Util\Shared;

use Codeception\PHPUnit\TestCase;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Constraint\LogicalNot;

trait Asserts
{
    protected function assert($arguments, $not = false)
    {
        $not = $not ? 'Not' : '';
        $method = ucfirst(array_shift($arguments));
        if (($method === 'True') && $not) {
            $method = 'False';
            $not = '';
        }
        if (($method === 'False') && $not) {
            $method = 'True';
            $not = '';
        }

        call_user_func_array(['\PHPUnit\Framework\Assert', 'assert' . $not . $method], $arguments);
    }

    protected function assertNot($arguments)
    {
        $this->assert($arguments, true);
    }

    /**
     * Checks that two variables are equal.
     *
     * @param        $expected
     * @param        $actual
     * @param string $message
     * @param float  $delta
     */
    protected function assertEquals($expected, $actual, $message = '', $delta = 0.0)
    {
        Assert::assertEquals($expected, $actual, $message, $delta);
    }

    /**
     * Checks that two variables are not equal
     *
     * @param        $expected
     * @param        $actual
     * @param string $message
     * @param float  $delta
     */
    protected function assertNotEquals($expected, $actual, $message = '', $delta = 0.0)
    {
        Assert::assertNotEquals($expected, $actual, $message, $delta);
    }

    /**
     * Checks that two variables are same
     *
     * @param        $expected
     * @param        $actual
     * @param string $message
     */
    protected function assertSame($expected, $actual, $message = '')
    {
        Assert::assertSame($expected, $actual, $message);
    }

    /**
     * Checks that two variables are not same
     *
     * @param        $expected
     * @param        $actual
     * @param string $message
     */
    protected function assertNotSame($expected, $actual, $message = '')
    {
        Assert::assertNotSame($expected, $actual, $message);
    }

    /**
     * Checks that actual is greater than expected
     *
     * @param        $expected
     * @param        $actual
     * @param string $message
     */
    protected function assertGreaterThan($expected, $actual, $message = '')
    {
        Assert::assertGreaterThan($expected, $actual, $message);
    }

    /**
     * Checks that actual is greater or equal than expected
     *
     * @param        $expected
     * @param        $actual
     * @param string $message
     */
    protected function assertGreaterThanOrEqual($expected, $actual, $message = '')
    {
        Assert::assertGreaterThanOrEqual($expected, $actual, $message);
    }

    /**
     * Checks that actual is less than expected
     *
     * @param        $expected
     * @param        $actual
     * @param string $message
     */
    protected function assertLessThan($expected, $actual, $message = '')
    {
        Assert::assertLessThan($expected, $actual, $message);
    }

    /**
     * Checks that actual is less or equal than expected
     *
     * @param        $expected
     * @param        $actual
     * @param string $message
     */
    protected function assertLessThanOrEqual($expected, $actual, $message = '')
    {
        Assert::assertLessThanOrEqual($expected, $actual, $message);
    }


    /**
     * Checks that haystack contains needle
     *
     * @param        $needle
     * @param        $haystack
     * @param string $message
     */
    protected function assertContains($needle, $haystack, $message = '')
    {
        Assert::assertContains($needle, $haystack, $message);
    }

    /**
     * Checks that haystack doesn't contain needle.
     *
     * @param        $needle
     * @param        $haystack
     * @param string $message
     */
    protected function assertNotContains($needle, $haystack, $message = '')
    {
        Assert::assertNotContains($needle, $haystack, $message);
    }

    /**
     * Checks that string match with pattern
     *
     * @param string $pattern
     * @param string $string
     * @param string $message
     */
    protected function assertRegExp($pattern, $string, $message = '')
    {
        TestCase::assertRegExp($pattern, $string, $message);
    }

    /**
     * Checks that string match with pattern
     *
     * Alias of assertRegExp
     * @param string $pattern
     * @param string $string
     * @param string $message
     */
    protected function assertMatchesRegularExpression($pattern, $string, $message = '')
    {
        TestCase::assertRegExp($pattern, $string, $message);
    }

    /**
     * Checks that string not match with pattern
     *
     * @param string $pattern
     * @param string $string
     * @param string $message
     */
    protected function assertNotRegExp($pattern, $string, $message = '')
    {
        TestCase::assertNotRegExp($pattern, $string, $message);
    }

    /**
     * Checks that string not match with pattern
     *
     * Alias of assertNotRegExp
     * @param string $pattern
     * @param string $string
     * @param string $message
     */
    protected function assertDoesNotMatchRegularExpression($pattern, $string, $message = '')
    {
        TestCase::assertNotRegExp($pattern, $string, $message);
    }

    /**
     * Checks that a string starts with the given prefix.
     *
     * @param string $prefix
     * @param string $string
     * @param string $message
     */
    protected function assertStringStartsWith($prefix, $string, $message = '')
    {
        Assert::assertStringStartsWith($prefix, $string, $message);
    }

    /**
     * Checks that a string doesn't start with the given prefix.
     *
     * @param string $prefix
     * @param string $string
     * @param string $message
     */
    protected function assertStringStartsNotWith($prefix, $string, $message = '')
    {
        Assert::assertStringStartsNotWith($prefix, $string, $message);
    }


    /**
     * Checks that variable is empty.
     *
     * @param        $actual
     * @param string $message
     */
    protected function assertEmpty($actual, $message = '')
    {
        Assert::assertEmpty($actual, $message);
    }

    /**
     * Checks that variable is not empty.
     *
     * @param        $actual
     * @param string $message
     */
    protected function assertNotEmpty($actual, $message = '')
    {
        Assert::assertNotEmpty($actual, $message);
    }

    /**
     * Checks that variable is NULL
     *
     * @param        $actual
     * @param string $message
     */
    protected function assertNull($actual, $message = '')
    {
        Assert::assertNull($actual, $message);
    }

    /**
     * Checks that variable is not NULL
     *
     * @param        $actual
     * @param string $message
     */
    protected function assertNotNull($actual, $message = '')
    {
        Assert::assertNotNull($actual, $message);
    }

    /**
     * Checks that condition is positive.
     *
     * @param        $condition
     * @param string $message
     */
    protected function assertTrue($condition, $message = '')
    {
        Assert::assertTrue($condition, $message);
    }

    /**
     * Checks that the condition is NOT true (everything but true)
     *
     * @param        $condition
     * @param string $message
     */
    protected function assertNotTrue($condition, $message = '')
    {
        Assert::assertNotTrue($condition, $message);
    }

    /**
     * Checks that condition is negative.
     *
     * @param        $condition
     * @param string $message
     */
    protected function assertFalse($condition, $message = '')
    {
        Assert::assertFalse($condition, $message);
    }

    /**
     * Checks that the condition is NOT false (everything but false)
     *
     * @param        $condition
     * @param string $message
     */
    protected function assertNotFalse($condition, $message = '')
    {
        Assert::assertNotFalse($condition, $message);
    }

    /**
     *
     * @param        $haystack
     * @param        $constraint
     * @param string $message
     */
    protected function assertThat($haystack, $constraint, $message = '')
    {
        Assert::assertThat($haystack, $constraint, $message);
    }

    /**
     * Checks that haystack doesn't attend
     *
     * @param        $haystack
     * @param        $constraint
     * @param string $message
     */
    protected function assertThatItsNot($haystack, $constraint, $message = '')
    {
        $constraint = new LogicalNot($constraint);
        Assert::assertThat($haystack, $constraint, $message);
    }

    
    /**
     * Checks if file exists
     *
     * @param string $filename
     * @param string $message
     */
    protected function assertFileExists($filename, $message = '')
    {
        Assert::assertFileExists($filename, $message);
    }


    /**
     * Checks if file doesn't exist
     *
     * @param string $filename
     * @param string $message
     */
    protected function assertFileNotExists($filename, $message = '')
    {
        TestCase::assertFileNotExists($filename, $message);
    }

    /**
     * Checks if file doesn't exist
     *
     * Alias of assertFileNotExists
     * @param string $filename
     * @param string $message
     */
    protected function assertFileDoesNotExist($filename, $message = '')
    {
        TestCase::assertFileNotExists($filename, $message);
    }

    /**
     * @param $expected
     * @param $actual
     * @param $description
     */
    protected function assertGreaterOrEquals($expected, $actual, $description = '')
    {
        Assert::assertGreaterThanOrEqual($expected, $actual, $description);
    }

    /**
     * @param $expected
     * @param $actual
     * @param $description
     */
    protected function assertLessOrEquals($expected, $actual, $description = '')
    {
        Assert::assertLessThanOrEqual($expected, $actual, $description);
    }

    /**
     * @param $actual
     * @param $description
     */
    protected function assertIsEmpty($actual, $description = '')
    {
        Assert::assertEmpty($actual, $description);
    }

    /**
     * @param $key
     * @param $actual
     * @param $description
     */
    protected function assertArrayHasKey($key, $actual, $description = '')
    {
        Assert::assertArrayHasKey($key, $actual, $description);
    }

    /**
     * @param $key
     * @param $actual
     * @param $description
     */
    protected function assertArrayNotHasKey($key, $actual, $description = '')
    {
        Assert::assertArrayNotHasKey($key, $actual, $description);
    }

    /**
     * Checks that array contains subset.
     *
     * @param array  $subset
     * @param array  $array
     * @param bool   $strict
     * @param string $message
     */
    protected function assertArraySubset($subset, $array, $strict = false, $message = '')
    {
        Assert::assertArraySubset($subset, $array, $strict, $message);
    }

    /**
     * @param $expectedCount
     * @param $actual
     * @param $description
     */
    protected function assertCount($expectedCount, $actual, $description = '')
    {
        Assert::assertCount($expectedCount, $actual, $description);
    }

    /**
     * @param $class
     * @param $actual
     * @param $description
     */
    protected function assertInstanceOf($class, $actual, $description = '')
    {
        Assert::assertInstanceOf($class, $actual, $description);
    }

    /**
     * @param $class
     * @param $actual
     * @param $description
     */
    protected function assertNotInstanceOf($class, $actual, $description = '')
    {
        Assert::assertNotInstanceOf($class, $actual, $description);
    }

    /**
     * @param $type
     * @param $actual
     * @param $description
     */
    protected function assertInternalType($type, $actual, $description = '')
    {
        Assert::assertInternalType($type, $actual, $description);
    }
    
    /**
     * Fails the test with message.
     *
     * @param $message
     */
    protected function fail($message)
    {
        Assert::fail($message);
    }

    protected function assertStringContainsString($needle, $haystack, $message = '')
    {
        TestCase::assertStringContainsString($needle, $haystack, $message);
    }

    protected function assertStringNotContainsString($needle, $haystack, $message = '')
    {
        TestCase::assertStringNotContainsString($needle, $haystack, $message);
    }

    protected function assertStringContainsStringIgnoringCase($needle, $haystack, $message = '')
    {
        TestCase::assertStringContainsStringIgnoringCase($needle, $haystack, $message);
    }

    protected function assertStringNotContainsStringIgnoringCase($needle, $haystack, $message = '')
    {
        TestCase::assertStringNotContainsStringIgnoringCase($needle, $haystack, $message);
    }

    /**
     * @since 1.1.0 of module-asserts
     */
    protected function assertStringEndsWith($suffix, $string, $message = '')
    {
        TestCase::assertStringEndsWith($suffix, $string, $message);
    }

    /**
     * @since 1.1.0 of module-asserts
     */
    protected function assertStringEndsNotWith($suffix, $string, $message = '')
    {
        TestCase::assertStringEndsNotWith($suffix, $string, $message);
    }

    protected function assertIsArray($actual, $message = '')
    {
        TestCase::assertIsArray($actual, $message);
    }

    protected function assertIsBool($actual, $message = '')
    {
        TestCase::assertIsBool($actual, $message);
    }

    protected function assertIsFloat($actual, $message = '')
    {
        TestCase::assertIsFloat($actual, $message);
    }

    protected function assertIsInt($actual, $message = '')
    {
        TestCase::assertIsInt($actual, $message);
    }

    protected function assertIsNumeric($actual, $message = '')
    {
        TestCase::assertIsNumeric($actual, $message);
    }

    protected function assertIsObject($actual, $message = '')
    {
        TestCase::assertIsObject($actual, $message);
    }

    protected function assertIsResource($actual, $message = '')
    {
        TestCase::assertIsResource($actual, $message);
    }

    protected function assertIsString($actual, $message = '')
    {
        TestCase::assertIsString($actual, $message);
    }

    protected function assertIsScalar($actual, $message = '')
    {
        TestCase::assertIsScalar($actual, $message);
    }

    protected function assertIsCallable($actual, $message = '')
    {
        TestCase::assertIsCallable($actual, $message);
    }

    protected function assertIsNotArray($actual, $message = '')
    {
        TestCase::assertIsNotArray($actual, $message);
    }

    protected function assertIsNotBool($actual, $message = '')
    {
        TestCase::assertIsNotBool($actual, $message);
    }

    protected function assertIsNotFloat($actual, $message = '')
    {
        TestCase::assertIsNotFloat($actual, $message);
    }

    protected function assertIsNotInt($actual, $message = '')
    {
        TestCase::assertIsNotInt($actual, $message);
    }

    protected function assertIsNotNumeric($actual, $message = '')
    {
        TestCase::assertIsNotNumeric($actual, $message);
    }

    protected function assertIsNotObject($actual, $message = '')
    {
        TestCase::assertIsNotObject($actual, $message);
    }

    protected function assertIsNotResource($actual, $message = '')
    {
        TestCase::assertIsNotResource($actual, $message);
    }

    protected function assertIsNotString($actual, $message = '')
    {
        TestCase::assertIsNotString($actual, $message);
    }

    protected function assertIsNotScalar($actual, $message = '')
    {
        TestCase::assertIsNotScalar($actual, $message);
    }

    protected function assertIsNotCallable($actual, $message = '')
    {
        TestCase::assertIsNotCallable($actual, $message);
    }

    protected function assertEqualsCanonicalizing($expected, $actual, $message = '')
    {
        TestCase::assertEqualsCanonicalizing($expected, $actual, $message);
    }

    protected function assertNotEqualsCanonicalizing($expected, $actual, $message = '')
    {
        TestCase::assertNotEqualsCanonicalizing($expected, $actual, $message);
    }

    protected function assertEqualsIgnoringCase($expected, $actual, $message = '')
    {
        TestCase::assertEqualsIgnoringCase($expected, $actual, $message);
    }

    protected function assertNotEqualsIgnoringCase($expected, $actual, $message = '')
    {
        TestCase::assertNotEqualsIgnoringCase($expected, $actual, $message);
    }

    protected function assertEqualsWithDelta($expected, $actual, $delta, $message = '')
    {
        TestCase::assertEqualsWithDelta($expected, $actual, $delta, $message);
    }

    protected function assertNotEqualsWithDelta($expected, $actual, $delta, $message = '')
    {
        TestCase::assertNotEqualsWithDelta($expected, $actual, $delta, $message);
    }
}
