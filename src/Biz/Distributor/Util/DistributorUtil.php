<?php

namespace Biz\Distributor\Util;

use Biz\Distributor\Common\DistributorCookieToolkit;
use AppBundle\Common\Exception\InvalidArgumentException;

class DistributorUtil
{
    public static function generateTokenByType($biz, $type, $params)
    {
        $distributorService = self::getDistributorServiceByType($biz, $type);

        return $distributorService->generateMockedToken($params);
    }

    public static function getDistributorServiceByToken($biz, $token)
    {
        $type = self::getTypeByToken($token);

        return self::getDistributorServiceByType($biz, $type);
    }

    public static function getProductIdByToken($token)
    {
        $splitedStr = explode(':', $token);

        return $splitedStr[1];
    }

    public static function getDistributorServiceByType($biz, $type)
    {
        if (!in_array($type, DistributorCookieToolkit::getTypes())) {
            throw new InvalidArgumentException('invalid type!');
        }

        return $biz->service('Distributor:Distributor'.ucfirst($type).'Service');
    }

    public static function getTypeByToken($token)
    {
        $splitedStr = explode(':', $token);

        return $splitedStr[0];
    }
}
