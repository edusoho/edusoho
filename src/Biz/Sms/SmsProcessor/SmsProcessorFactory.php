<?php

namespace Biz\Sms\SmsProcessor;

use Biz\Sms\SmsException;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
use Topxia\Service\Common\ServiceKernel;

class SmsProcessorFactory
{
    private static $mockedProcessor; //单元测试用

    /**
     * @param $targetType
     *
     * @return BaseSmsProcessor
     *
     * @throws InvalidArgumentException
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
