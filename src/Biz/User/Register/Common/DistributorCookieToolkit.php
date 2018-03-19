<?php

namespace Biz\User\Register\Common;

use Symfony\Component\HttpFoundation\Cookie;
use AppBundle\Common\TimeMachine;

class DistributorCookieToolkit
{
    public static function setTokenToCookie($response, $token)
    {
        $cookie = new Cookie('distributor-token', $token, TimeMachine::time() + 604800);
        $response->headers->setCookie($cookie); //有效期7天

        return $response;
    }

    public static function setCookieTokenToFields($request, $fields)
    {
        $distributorTokenCookie = $request->cookies->get('distributor-token');
        if (!empty($distributorTokenCookie)) {
            $fields['distributorToken'] = $distributorTokenCookie;
        }

        return $fields;
    }

    public static function clearCookieToken($request, $response)
    {
        $distributorTokenCookie = $request->cookies->get('distributor-token');
        if (!empty($distributorTokenCookie)) {
            $response->headers->setCookie(new Cookie('distributor-token', ''));
        }

        return $response;
    }
}
