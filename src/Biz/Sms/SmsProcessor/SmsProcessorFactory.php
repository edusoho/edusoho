<?php

namespace Biz\Sms\SmsProcessor;

use Biz\Sms\SmsException;
use Topxia\Service\Common\ServiceKernel;

class SmsProcessorFactory
{
    private static $mockedProcessor; //单元测试用

    /**
     * @param $targetType
     *
     * @return BaseSmsProcessor
     *
     * @throws SmsException
     */
    public static function create($targetType)
    {
        if (empty(self::$mockedProcessor)) {
            if (empty($targetType)) {
                throw SmsException::ERROR_SMS_TYPE();
            }

            $class = __NAMESPACE__.'\\'.ucfirst($targetType).'SmsProcessor';

            return new $class(ServiceKernel::instance()->getBiz());
        }

        return self::$mockedProcessor;
    }
}
