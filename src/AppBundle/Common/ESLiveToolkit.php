<?php

namespace AppBundle\Common;

class ESLiveToolkit
{
    public static function generateCallback($baseUrl, $token)
    {
        $courseWareUrl = "{$baseUrl}/callback/ESLive?ac=courseWare.fetch&jwtToken={$token}";

        return $courseWareUrl;
    }
}
