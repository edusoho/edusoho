<?php

namespace ApiBundle\Api\Util;

use Topxia\Service\Common\ServiceKernel;

class Money
{
    private static $instance;

    private $coinSetting;

    private function __construct()
    {
        ServiceKernel::instance()->createService('System:SettingService')->get('coin');
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    /**
     * @param $timestamp
     * @param string $format
     */
    public static function convert($price)
    {

    }
}