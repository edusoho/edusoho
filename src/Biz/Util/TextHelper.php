<?php

namespace Biz\Util;

class TextHelper
{
    public static function truncate($text, $length = 100, $ellipsis = '...')
    {
        $text = strip_tags($text);
        $text = str_replace(array("\n", "\r", "\t", '&nbsp;'), '', $text);

        if (mb_strlen($text, 'UTF-8') > $length) {
            $text = mb_substr($text, 0, $length, 'UTF-8');
            $text .= '...';
        }

        return $text;
    }
}
