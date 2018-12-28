<?php

namespace Tests\Unit\Sms\SmsProcessor;

use Biz\BaseTestCase;
use Biz\Sms\SmsProcessor\SmsProcessorFactory;
use Biz\Sms\SmsProcessor\TaskSmsProcessor;

class SmsProcessorFactoryTest extends BaseTestCase
{
    public function testCreate()
    {
        $result = SmsProcessorFactory::create('LiveOpenLesson');
        $this->assertEquals('Biz\Sms\SmsProcessor\LiveOpenLessonSmsProcessor', get_class($result));
        $class = SmsProcessorFactory::create('task');
        $this->assertEquals(true, $class instanceof TaskSmsProcessor);
    }

    /**
     * @throws \Biz\Sms\SmsException
     * @expectedException \Biz\Sms\SmsException
     * @expectedExceptionMessage exception.sms.type_error
     */
    public function testCreateWithException()
    {
        SmsProcessorFactory::create('');
    }
}
