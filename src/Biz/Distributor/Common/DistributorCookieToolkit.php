<?php

namespace Biz\Distributor\Common;

use Symfony\Component\HttpFoundation\Cookie;
use AppBundle\Common\TimeMachine;

class DistributorCookieToolkit
{
    /** 注意，新增一种类型时， 需要修改 getTypes()方法， 往getTypes()内新增一项类型 */
    const USER = 'user';

    const COURSE = 'courseOrder';

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
        $fields['distributorToken'] = self::getCookieToken($request, $cookieName);

        return $fields;
    }

    public static function getCookieToken($request, $cookieName, $defaultValue = '')
    {
        $distributorTokenCookie = $request->cookies->get("distributor-{$cookieName}-token");
        if (!empty($distributorTokenCookie)) {
            return $distributorTokenCookie;
        }

        return $defaultValue;
    }

    public static function clearCookieToken($request, $response, $cookieName = null)
    {
        if (empty($cookieName)) {
            $clearedTypes = self::getTypes();
        } else {
            $clearedTypes = array($cookieName);
        }

        foreach ($clearedTypes as $type) {
            $distributorTokenCookie = $request->cookies->get("distributor-{$type}-token");
            if (!empty($distributorTokenCookie)) {
                $response->headers->setCookie(new Cookie("distributor-{$type}-token", ''));
            }
        }

        return $response;
    }

    public static function getTypes()
    {
        return array(self::USER, self::COURSE);
    }
}
