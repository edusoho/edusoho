<?php

namespace Canoma;

/**
 * @author Mark van der Velden <mark@dynom.nl>
 *
 * This method implements the compare method, since it's straight forward for small numbers it's convenient to re-use.
 * Simply extend and voila. However the combination of PHP and large numbers is a problem. To solve this, the comparison
 * logic is placed in the adapters, so that the problem can be solved when needed.
 */
abstract class HashAdapterAbstract
{
    /**
     * Compare two arguments, this method can be overridden per adapter basis to handle larger numbers.
     *
     * returns -1 if $left is smaller than $right,
     * returns 0 if both are equal or
     * returns +1 when $left is greater than $right
     *
     * @param $left
     * @param $right
     *
     * @return int
     */
    public function compare($left, $right)
    {
        if ($left === $right) {
            return 0;
        } else if ($left < $right) {
            return -1;
        } else {
            return 1;
        }
    }


    /**
     * A method to test if we're on a 32 bit OS and should perhaps implement workarounds
     *
     * @return bool
     */
    public function is32bitOS()
    {
        return (PHP_INT_MAX == '2147483647');
    }
}
