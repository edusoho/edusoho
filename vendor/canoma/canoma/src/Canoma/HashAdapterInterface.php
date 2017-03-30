<?php

namespace Canoma;

/**
 * @author Mark van der Velden <mark@dynom.nl>
 */
interface HashAdapterInterface
{

    /**
     * Convert the argument (a string) to a hexadecimal value, using a adapter-specific algorithm. The return value
     * should be a string.
     *
     * @abstract
     * @param string $string
     * @return string
     */
    public function hash($string);

    /**
     * Compare two arguments, expected behavior:
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
    public function compare($left, $right);
}
