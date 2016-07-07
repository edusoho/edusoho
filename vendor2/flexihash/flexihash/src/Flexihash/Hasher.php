<?php

/**
 * Hashes given values into a sortable fixed size address space.
 *
 * @author Paul Annesley
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
interface Flexihash_Hasher
{
    /**
     * Hashes the given string into a 32bit address space.
     *
     * Note that the output may be more than 32bits of raw data, for example
     * hexidecimal characters representing a 32bit value.
     *
     * The data must have 0xFFFFFFFF possible values, and be sortable by
     * PHP sort functions using SORT_REGULAR.
     *
     * @param string
     * @return mixed A sortable format with 0xFFFFFFFF possible values
     */
    public function hash($string);
}
