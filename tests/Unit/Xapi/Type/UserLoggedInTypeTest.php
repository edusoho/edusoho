<?php

namespace Tests\Unit\Xapi\Type;

use Biz\BaseTestCase;
use Biz\Xapi\Type\UserLoggedInType;

class UserLoggedInTypeTest extends BaseTestCase
{
    public function testPackages()
    {
        $this->getSettingService()->set('storage', array(
            'cloud_access_key' => 1,
            'cloud_secret_key' => 2,
        ));

        $type = new UserLoggedInType();
        $type->setBiz($this->biz);

        $statements = array(
            array('user_id' => 1, 'uuid' => 10, 'occur_time' => time()),
            array('user_id' => 2, 'uuid' => 20, 'occur_time' => time()),
        );
        $pushStatements = $type->packages($statements);

        $this->assertEquals(array('id', 'actor', 'verb', 'object', 'timestamp'), array_keys($pushStatements[0]));

        $this->assertEquals('http://activitystrea.ms/schema/1.0/application', $pushStatements[0]['object']['definition']['type']);
        $this->assertEquals('https://w3id.org/xapi/adl/verbs/logged-in', $pushStatements[1]['verb']['id']);
    }

    /**
     * @return \Biz\System\Service\SettingService
     */
    private function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
