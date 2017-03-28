<?php

namespace Canoma\HashAdapter;

use \Canoma\HashAdapterInterface;
use \Canoma\HashAdapterAbstract;

/**
 * The Adler32 algorithm is very fast, but the coverage is quite poor when dealing with small keys. Only use this
 * algorithm when you want speed and when you're dealing with fairly large keys.
 *
 * @author Mark van der Velden <mark@dynom.nl>
 */
class Adler32 extends HashAdapterAbstract implements HashAdapterInterface
{
    /**
     * Convert the argument (a string) to a hexadecimal value, using the Adler32 algorithm.
     *
     * @param string $string
     *
     * @return string
     */
    public function hash($string)
    {
        return hash(
            'adler32',
            $string
        );
    }
}
