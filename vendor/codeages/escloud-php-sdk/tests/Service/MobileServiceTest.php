<?php

namespace ESCloud\SDK\Tests\Service;

use ESCloud\SDK\Service\MobileService;
use ESCloud\SDK\Tests\BaseTestCase;

class MobileServiceTest extends BaseTestCase
{
    public function testGetAppleToken()
    {
        $httpClient = $this->mockHttpClient(array(
            'access_token' => 'access_token',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'refresh_token' => 'refresh_token',
            'id_token' => 'id_token',
        ));

        $service = new MobileService($this->auth, array(), null, $httpClient);

        $result = $service->getAppleToken('test');

        $this->assertEquals('id_token', $result['id_token']);
    }
}