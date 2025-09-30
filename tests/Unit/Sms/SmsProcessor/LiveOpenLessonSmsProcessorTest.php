<?php

namespace Tests\Unit\Sms\SmsProcessor;

use AppBundle\Common\ReflectionUtils;
use AppBundle\Common\SmsToolkit;
use Biz\BaseTestCase;
use Biz\Sms\SmsProcessor\LiveOpenLessonSmsProcessor;
use Biz\System\Service\SettingService;

class LiveOpenLessonSmsProcessorTest extends BaseTestCase
{
    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }
}
