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
     * @throws \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     * @expectedExceptionMessage 短信类型不存在
     */
    public function testCreateWithException()
    {
        SmsProcessorFactory::create('');
    }
}
