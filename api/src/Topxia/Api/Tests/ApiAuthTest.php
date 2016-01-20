<?php

namespace Topxia\Api\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Api\ApiAuth;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\ServiceKernel;

class ApiAuthTest extends BaseTestCase
{

    public function testEncodeKeysign()
    {
        $settings = array(
            'cloud_access_key' => 'testkey',
            'cloud_secret_key' => 'testsecret',
        );

        $this->getSettingService()->set('storage', $settings);

        $auth = new ApiAuth();

        $request = Request::create('/?a=b', 'GET', array(), array(), array(), array());
        $token = $auth->encodeKeysign($request);

        $request->headers->set('X-Auth-Method', 'keysign');
        $request->headers->set('X-Auth-Token', $token);

        $auth->auth($request);

        exit();


        $decoded = $auth->decodeKeysign($token);

        var_dump($decoded);
    }

    private function getSettingService()
    {
        return ServiceKernel::instance()->createService('System.SettingService');
    }


}