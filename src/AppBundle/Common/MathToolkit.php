<?php

namespace AppBundle\Common;

class MathToolkit
{
    public static function multiply($data, $fields, $multiplicator)
    {
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $data[$field] *= $multiplicator;
            }
        }

        return $data;
    }

    public static function simple($number, $multiplicator)
    {
        return $number * $multiplicator;
    }

    public static function isEqual($number1, $number2)
    {
        return abs($number1 - $number2) < 0.00001;
    }

    public static function uniqid($prefix = 'ES')
    {
        return md5(uniqid($prefix, rand(0, 10000)));
    }
}
