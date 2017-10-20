<?php

namespace AppBundle\Common;

class NumberToolkit
{
    public static function roundUp($value, $precision = 2)
    {
        $mult = pow(10, $precision);

        return ceil($value * $mult) / $mult;
    }
}
