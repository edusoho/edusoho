<?php

namespace Tests\Unit\Sms;

use Biz\BaseTestCase;
use Biz\Sms\SmsProcessor\TaskSmsProcessor;
use AppBundle\Common\ReflectionUtils;
use AppBundle\Common\SmsToolkit;

class TaskSmsProcessorTest extends BaseTestCase
{

    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }
}
