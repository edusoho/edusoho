<?php

namespace ESCloud\SDK\Tests\Service;

use ESCloud\SDK\Service\BaseService;
use ESCloud\SDK\Service\ScrmService;
use ESCloud\SDK\Tests\BaseTestCase;

class ScrmServiceTest extends BaseTestCase
{
    public function testGetUserByToken()
    {
        $mock = [
            "realName" => "孔子",
            "workWechatName" => "孔子",
            "address" => "jkfdjsioejlkf",
            "sex" => 1,
            "qq" => "37328732",
            "phone" => "15757126364",
            "email" => "kongzi@edusoho.com"
        ];
        $httpClient = $this->mockHttpClient($mock);
        $service = new ScrmService($this->auth, array(), null, $httpClient);
        $result = $service->getUserByToken('12345');

        self::assertEquals('孔子', $result['realName']);
    }
}
