<?php

namespace ApiBundle\Api\Util;
class Converter
{
    public static function timestampToDate(&$timestamp, $format = 'c')
    {
        if ($timestamp) {
            $timestamp = date($timestamp, 'c');
        } else {
            $timestamp = '0';
        }
    }
}