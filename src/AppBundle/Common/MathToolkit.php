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
}
