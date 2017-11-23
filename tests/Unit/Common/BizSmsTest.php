<?php

namespace Tests\Unit\Common;

use Biz\BaseTestCase;
use Biz\Common\BizSms;

class BizSmsTest extends BaseTestCase
{
    public function testSend()
    {
        $mobile = '15012345678';
        $smsType = BizSms::SMS_BIND_TYPE;
        $code = '123456';
        $this->mockBiz('Sms:SmsService', array(
            array('functionName' => 'sendVerifySms', 'withParams' => array($smsType, $mobile, 0), 'returnValue' => array('captcha_code' => $code))
        ));

        $options = array(
            'times' => 20,
            'userId' => 1,
            'duration' => 120,
        );

        $token = $this->getBizSms()->send($smsType, $mobile, $options);
        $this->assertArraySubset(array('times' => $options['times'], 'userId' => $options['userId']), $token);
        $this->assertEquals(array('code' => $code, 'mobile' => $mobile), $token['data']);
    }

    /**
     * @return \Biz\Common\BizSms
     */
    private function getBizSms()
    {
        return $this->biz['biz_sms'];
    }
}
