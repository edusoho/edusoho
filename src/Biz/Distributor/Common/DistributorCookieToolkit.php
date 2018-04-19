<?php

namespace Biz\Distributor\Common;

use Symfony\Component\HttpFoundation\Cookie;
use AppBundle\Common\TimeMachine;

class DistributorCookieToolkit
{
    public static function setTokenToCookie($response, $token, $cookieName, $liveTime = 604800)
    {
        if ($liveTime) {
            $cookie = new Cookie("distributor-{$cookieName}-token", $token, TimeMachine::time() + $liveTime);
        } else {
            $cookie = new Cookie("distributor-{$cookieName}-token", $token);
        }

        $response->headers->setCookie($cookie);

        return $response;
    }

    public static function setCookieTokenToFields($request, $fields, $cookieName)
    {
        $distributorTokenCookie = $request->cookies->get("distributor-{$cookieName}-token");
        if (!empty($distributorTokenCookie)) {
            $fields['distributorToken'] = $distributorTokenCookie;
        }

        return $fields;
    }

    public static function clearCookieToken($request, $response, $cookieName)
    {
        $distributorTokenCookie = $request->cookies->get("distributor-{$cookieName}-token");
        if (!empty($distributorTokenCookie)) {
            $response->headers->setCookie(new Cookie("distributor-{$cookieName}-token", ''));
        }

        return $response;
    }
}
