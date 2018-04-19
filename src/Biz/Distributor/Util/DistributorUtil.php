<?php

namespace Biz\Distributor\Util;

use Biz\Distributor\Common\DistributorCookieToolkit;
use AppBundle\Common\Exception\InvalidArgumentException;
use Topxia\Service\Common\ServiceKernel;

class DistributorUtil
{
    public static function getType($token)
    {
        $splitedStr = explode(':', $token);

        return $splitedStr[1] ? $splitedStr[1] : 'course';
    }

    public static function generateTokenByType($type, $params)
    {
        if (!in_array($type, DistributorCookieToolkit::TYPE)) {
            return false;
        }

        switch ($type) {
            case DistributorCookieToolkit::USER:
                $data = array(
                    'merchant_id' => '123',
                    'agency_id' => '22221',
                    'coupon_price' => $params['couponPrice'],
                    'coupon_expiry_day' => $params['couponExpiryDay'],
                );
                $tokenExpireDateNum = strtotime($params['tokenExpireDateStr']);
                break;
            case DistributorCookieToolkit::COURSE:
                $data = array(
                    'org_id' => $params['orgId'],
                    'type' => $params['type'],
                    'course_id' => $params['courseId'],
                    'merchant_id' => '123',
                );
                $tokenExpireDateNum = null;
                break;
            default:
                throw new InvalidArgumentException('invalid type!');
                break;
        }

        return self::getDistributorUserService()->encodeToken($data, $tokenExpireDateNum);
    }

    protected static function getDistributorUserService()
    {
        return self::getServiceKernel()->getBiz()->service('Distributor:DistributorUserService');
    }

    protected static function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}
