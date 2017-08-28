<?php

namespace AppBundle\Common;

class MathToolkit
{
    public static function multiply($data, $files, $multiplicator)
    {
        foreach ($files as $file) {
            if (isset($data[$file])) {
                $data[$file] *= $multiplicator;
            }
        }

        return $data;
    }
}
