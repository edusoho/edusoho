<?php

namespace Tests\Unit\Xapi\Type;

use Biz\BaseTestCase;
use Biz\Xapi\Type\UserRegisteredType;

class UserRegisteredTypeTest extends BaseTestCase
{
    public function testPackages()
    {
        $this->getSettingService()->set('storage', array(
            'cloud_access_key' => 1,
            'cloud_secret_key' => 2,
        ));

        $type = new UserRegisteredType();
        $type->setBiz($this->biz);

        $statements = array(
            array('user_id' => 1, 'uuid' => 10, 'target_type' => 'user', 'occur_time' => time()),
            array('user_id' => 2, 'uuid' => 20, 'target_type' => 'user', 'occur_time' => time()),
        );
        $pushStatements = $type->packages($statements);

        $this->assertEquals(array('id', 'actor', 'verb', 'timestamp'), array_keys($pushStatements[0]));
        foreach ($statements as $index => $st) {

            $this->assertEquals(array('id' => 'http://adlnet.gov/expapi/verbs/registered', 'display' => array(
                'zh-CN' => '注册了', 'en-US' => 'registered'
            )), $st['verb']);
        }
    }

    /**
     * @return \Biz\System\Service\SettingService
     */
    private function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
