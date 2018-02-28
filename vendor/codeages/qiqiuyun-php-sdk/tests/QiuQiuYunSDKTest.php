<?php

namespace QiQiuYun\SDK\Tests\Service;

use QiQiuYun\SDK\Tests\BaseTestCase;
use QiQiuYun\SDK\QiQiuYunSDK;

class QiQiuYunSDKTest extends BaseTestCase
{
    public function testGetService()
    {
        $sdk = new QiQiuYunSDK(array(
            'access_key' => $this->accessKey,
            'secret_key' => $this->secretKey,
            'service' => array(
                'xapi' => array(
                    'school_name' => '测试网校',
                ),
            ),
        ));

        $this->assertInstanceOf('QiQiuYun\\SDK\\Service\\SmsService', $sdk->getSmsService());
        $this->assertInstanceOf('QiQiuYun\\SDK\\Service\\PlayService', $sdk->getPlayService());
        $this->assertInstanceOf('QiQiuYun\\SDK\\Service\\XAPIService', $sdk->getXAPIService());
        $this->assertInstanceOf('QiQiuYun\\SDK\\Service\\DrpService', $sdk->getDrpService());
    }
}
