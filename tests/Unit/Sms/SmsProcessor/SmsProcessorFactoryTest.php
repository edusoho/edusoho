<?php

namespace Tests\Unit\Sms\SmsProcessor;

use Biz\BaseTestCase;
use Biz\Sms\SmsProcessor\SmsProcessorFactory;

class SmsProcessorFactoryTest extends BaseTestCase
{
    public function testCreate()
    {
        $result = SmsProcessorFactory::create('LiveOpenLesson');
        $this->assertEquals('Biz\Sms\SmsProcessor\LiveOpenLessonSmsProcessor', get_class($result));
    }
}
