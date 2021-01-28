<?php

namespace Tests\Unit\AppBundle\Util;

use Biz\BaseTestCase;
use AppBundle\Util\UploaderToken;
use AppBundle\Common\TimeMachine;
use Biz\System\Service\SettingService;
use Firebase\JWT\JWT;

class UploaderTokenTest extends BaseTestCase
{
    public function testMake()
    {
        $user = $this->getCurrentUser();
        TimeMachine::setMockedTime(time());
        $uploaderToken = new UploaderToken();
        $token = $uploaderToken->make('courselesson', 3, 'private');
        $this->getSettingService()->get('storage', array());
        $accessKey = empty($storage['cloud_access_key']) ? '' : $storage['cloud_access_key'];
        $secretKey = empty($storage['cloud_secret_key']) ? '' : $storage['cloud_secret_key'];
        $result = JWT::decode($token, md5($accessKey.$secretKey), array('HS256'));
        $contents = explode('|', $result->metas);
        $this->assertEquals($user['uuid'], $contents[0]);
        $this->assertEquals('courselesson', $contents[1]);
        $this->assertEquals(3, $contents[2]);
        $this->assertEquals('private', $contents[3]);
    }

    public function testParse()
    {
        $uploaderToken = new UploaderToken();
        //传入空
        $result = $uploaderToken->parse(null);
        $this->assertNull($result);

        //正常传入的token
        $user = $this->getCurrentUser();
        $token = $uploaderToken->make('courselesson', 3, 'private');
        $result = $uploaderToken->parse($token);
        $this->assertEquals($user['id'], $result['userId']);
        $this->assertEquals('courselesson', $result['targetType']);
        $this->assertEquals(3, $result['targetId']);
        $this->assertEquals('private', $result['bucket']);
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
