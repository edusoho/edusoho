<?php

namespace AppBundle\Common;

class NumberToolkit
{
    public static function roundUp($value, $precision = 2)
    {
        return round($value, $precision, PHP_ROUND_HALF_UP);
    }
}
