<?php

namespace Biz\Distributor\Util;

use Symfony\Component\HttpFoundation\Cookie;
use AppBundle\Common\TimeMachine;

class DistributorCookieToolkit
{
    /** 注意，新增一种类型时， 需要修改 getTypes()方法， 往getTypes()内新增一项类型 */
    const USER = 'user';

    const PRODUCT_ORDER = 'productOrder';

    public static function setTokenToCookie($response, $token, $cookieType, $liveTime = 604800)
    {
        if ($liveTime) {
            $cookie = new Cookie(self::getCookieName($cookieType), $token, TimeMachine::time() + $liveTime);
        } else {
            $cookie = new Cookie(self::getCookieName($cookieType), $token);
        }

        $response->headers->setCookie($cookie);

        return $response;
    }

    public static function setCookieTokenToFields($request, $fields, $cookieType)
    {
        $fields['distributorToken'] = self::getCookieToken($request, $cookieType);

        return $fields;
    }

    public static function getCookieToken($request, $cookieType, $defaultValue = '')
    {
        $distributorTokenCookie = $request->cookies->get(self::getCookieName($cookieType));
        if (!empty($distributorTokenCookie)) {
            return $distributorTokenCookie;
        }

        return $defaultValue;
    }

    public static function clearCookieToken($request, $response, $cookieType = null)
    {
        if (empty($cookieType)) {
            $clearedTypes = self::getTypes();
        } else {
            $clearedTypes = array($cookieType);
        }

        foreach ($clearedTypes as $type) {
            $cookieName = self::getCookieName($type);
            $distributorTokenCookie = $request->cookies->get($cookieName);
            if (!empty($distributorTokenCookie)) {
                $response->headers->setCookie(new Cookie($cookieName, ''));
            }
        }

        return $response;
    }

    /**
     * @param $cookieType 目前只有 DistributorCookieToolkit::USER 和 DistributorCookieToolkit::PRODUCT_ORDER
     */
    public static function getCookieName($cookieType)
    {
        return "distributor-{$cookieType}-token";
    }

    private static function getTypes()
    {
        return array(self::USER, self::PRODUCT_ORDER);
    }
}
