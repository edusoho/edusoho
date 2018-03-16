<?php

namespace Tests\Unit\User;

use AppBundle\Common\ReflectionUtils;
use Biz\BaseTestCase;
use Biz\User\CurrentUser;

class UserActiveServiceTest extends BaseTestCase
{
    public function testCreateActiveUserWithNotLogin()
    {
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 0,
            'nickname' => 'admin1',
            'email' => 'admin3@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER', 'ROLE_ADMIN'),
        ));
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $result = ReflectionUtils::invokeMethod($this->getUserActiveService(), 'createActiveUser');
        $this->assertEquals(array(), $result);
    }

    public function testIsActiveUser()
    {
        $this->mockBiz(
            'User:UserActiveDao',
            array(
                array(
                    'functionName' => 'getByUserId',
                    'returnValue' => array('id' => 2, 'userId' => 1),
                    'withParams' => array(1),
                ),
            )
        );
        $result = ReflectionUtils::invokeMethod($this->getUserActiveService(), 'isActiveUser');
        $this->assertTrue($result);
    }

    public function testAnalysisActiveUser()
    {
        $this->mockBiz(
            'User:UserActiveDao',
            array(
                array(
                    'functionName' => 'analysis',
                    'returnValue' => array(array('userId' => 1, 'date' => '20171022')),
                    'withParams' => array(5000, 6000),
                ),
            )
        );
        $result = $this->getUserActiveService()->analysisActiveUser(5000, 6000);
        $this->assertEquals(array(array('userId' => 1, 'date' => '20171022')), $result);
    }

    public function testSaveOnline()
    {
        $this->mockBiz('Session:OnlineService', array(
            array(
                'functionName' => 'saveOnline',
                'returnValue' => 1
            )
        ));
        $this->getUserActiveService()->saveOnline(array('user_id' => 1900));
        $result = $this->getUserActiveDao()->getByUserId(1900);
        $this->assertNotNull($result);
        $this->assertEquals(1900, $result['userId']);
    }

    /**
     * @return \Biz\User\Dao\UserActiveDao
     */
    private function getUserActiveDao()
    {
        return $this->createDao('User:UserActiveDao');
    }
    /**
     * @return \Biz\User\Service\UserActiveService
     */
    protected function getUserActiveService()
    {
        return $this->createService('User:UserActiveService');
    }
}
