<?php

namespace AppBundle\Common;

class UrlToolkit
{
    public static function ltrimHttpProtocol(string $url)
    {
        return preg_replace('/^(https?:\/\/)/i', '', $url);
    }
}
