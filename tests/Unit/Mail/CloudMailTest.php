<?php

namespace Tests\Unit\Mail;

use Biz\BaseTestCase;
use Biz\Mail\CloudMail;
use AppBundle\Common\ReflectionUtils;
use Biz\CloudPlatform\CloudAPIFactory;
use Tests\Unit\Mail\Tool\MockedApi;

class CloudMailTest extends BaseTestCase
{
    public function testDoSendWithStatusOff()
    {
        $mail = new CloudMail(array());
        $mail->setBiz($this->biz);
        $this->assertFalse($mail->doSend());
    }

    public function testDoSend()
    {
        $settingService = $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'params' => array('cloud_email_crm', array()),
                    'returnValue' => array('status' => 'enable'),
                ),
            )
        );

        $mail = new CloudMail(array(
            'title' => 'email_title',
            'body' => 'email_body',
            'type' => 'market',
            'sendedSn' => 'email_sn',
            'template' => 'mame',
        ));
        $mail->setBiz($this->biz);

        $mockedApi = new MockedApi();
        ReflectionUtils::setStaticProperty(new CloudAPIFactory(), 'api', $mockedApi);

        $result = $mail->doSend();

        $settingService->shouldHaveReceived('get');

        $this->assertEquals('/emails', $mockedApi->getUri());
        $this->assertArrayEquals(
            array(
                'title' => 'email_title',
                'body' => 'email_body',
                'format' => 'text',
                'template' => 'email_default',
                'type' => 'market',
                'sendedSn' => 'email_sn',
            ),
            $mockedApi->getParams()
        );

        ReflectionUtils::setStaticProperty(new CloudAPIFactory(), 'api', null);
    }
}
