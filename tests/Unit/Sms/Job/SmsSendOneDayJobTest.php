<?php

namespace Tests\Unit\Sms\Job;

use Biz\BaseTestCase;
use Biz\Sms\Job\SmsSendOneDayJob;
use AppBundle\Common\ReflectionUtils;
use Biz\Sms\SmsProcessor\SmsProcessorFactory;
use Biz\CloudPlatform\CloudAPIFactory;

class SmsSendOneDayJobTest extends BaseTestCase
{
    public function testExecute()
    {
        $smsService = $this->mockBiz(
            'Sms:SmsService',
            array(
                array(
                    'functionName' => 'isOpen',
                    'withParams' => array('sms_live_play_one_day'),
                    'returnValue' => true,
                ),
            )
        );

        $mockedProcessor = $this->mockBiz(
            'Mocked:MockedProcessor',
            array(
                array(
                    'functionName' => 'getUrls',
                    'withParams' => array(112, 'sms_live_play_one_day'),
                    'returnValue' => array(
                        'urls' => 'http://www.demo.com/abc',
                        'count' => 2183,
                    ),
                ),
            )
        );

        $mockedApi = $this->mockBiz(
            'Mocked:MockedApiOne',
            array(
                array(
                    'functionName' => 'post',
                    'withParams' => array('/sms/sendBatch', array('total' => 2, 'callbackUrls' => 'http://www.demo.com/abc')),
                ),
            )
        );

        ReflectionUtils::setStaticProperty(new SmsProcessorFactory(), 'mockedProcessor', $mockedProcessor);
        ReflectionUtils::setStaticProperty(new CloudAPIFactory(), 'api', $mockedApi);

        $job = new SmsSendOneDayJob(array(), $this->biz);
        $job->args = array('targetType' => 'LiveOpenLesson', 'targetId' => 112);
        $job->execute();

        $smsService->shouldHaveReceived('isOpen')->times(1);
        $mockedProcessor->shouldHaveReceived('getUrls')->times(1);
        $mockedApi->shouldHaveReceived('post')->times(1);

        $this->assertTrue(true);

        ReflectionUtils::setStaticProperty(new SmsProcessorFactory(), 'mockedProcessor', null);
        ReflectionUtils::setStaticProperty(new CloudAPIFactory(), 'api', null);
    }

    public function testExecuteWithException()
    {
        $smsService = $this->mockBiz(
            'Sms:SmsService',
            array(
                array(
                    'functionName' => 'isOpen',
                    'withParams' => array('sms_live_play_one_day'),
                    'returnValue' => true,
                ),
            )
        );

        $logService = $this->mockBiz(
            'System:LogService',
            array(
                array(
                    'functionName' => 'error',
                ),
            )
        );

        $job = new SmsSendOneDayJob(array(), $this->biz);
        $job->args = array('targetType' => '', 'targetId' => 112);
        $job->execute();

        $smsService->shouldHaveReceived('isOpen')->times(1);
        $logService->shouldHaveReceived('error')->times(1);
        $this->assertTrue(true);
    }
}
