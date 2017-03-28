<?php

namespace Canoma\HashAdapter;

use \Canoma\HashAdapterInterface;
use \Canoma\HashAdapterAbstract;

/**
 * @author Mark van der Velden <mark@dynom.nl>
 */
class Salsa20 extends HashAdapterAbstract implements HashAdapterInterface
{
    /**
     * Convert the argument (a string) to a hexadecimal value, using the Salsa20 algorithm.
     * @see http://cr.yp.to/snuffle.html
     *
     * @param string $string
     *
     * @return string
     */
    public function hash($string)
    {
        return hash(
            'salsa20',
            $string
        );
    }
}
