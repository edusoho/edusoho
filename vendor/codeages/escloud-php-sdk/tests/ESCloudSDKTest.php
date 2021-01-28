<?php

namespace ESCloud\SDK\Tests\Service;

use ESCloud\SDK\Tests\BaseTestCase;
use ESCloud\SDK\ESCloudSDK;

class ESCloudSDKTest extends BaseTestCase
{
    public function testGetService()
    {
        $sdk = new ESCloudSDK(array(
            'access_key' => $this->accessKey,
            'secret_key' => $this->secretKey,
            'service' => array(
                'xapi' => array(
                    'school_name' => '测试网校',
                ),
            ),
        ));

        $this->assertInstanceOf('ESCloud\\SDK\\Service\\AiService', $sdk->getAiService());
        $this->assertInstanceOf('ESCloud\\SDK\\Service\\SmsService', $sdk->getSmsService());
        $this->assertInstanceOf('ESCloud\\SDK\\Service\\PlayService', $sdk->getPlayService());
        $this->assertInstanceOf('ESCloud\\SDK\\Service\\XAPIService', $sdk->getXAPIService());
        $this->assertInstanceOf('ESCloud\\SDK\\Service\\DrpService', $sdk->getDrpService());
    }
}
