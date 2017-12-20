<?php

namespace Tests\Unit\User;

use Biz\BaseTestCase;
use Biz\User\CurrentUser;

class UserActiveServiceTest extends BaseTestCase
{
    public function testCreateActiveUser()
    {
        $result = $this->getUserActiveService()->createActiveUser();
        $this->assertEquals(1, $result['userId']);
    }

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
        $result = $this->getUserActiveService()->createActiveUser();
        $this->assertEquals(array(), $result);
    }

    public function testGetActiveUser()
    {
        $this->mockBiz(
            'User:UserActiveDao',
            array(
                array(
                    'functionName' => 'getByUserId',
                    'returnValue' => array('id' => 2, 'userId' => 3),
                    'withParams' => array(3),
                ),
            )
        );
        $result = $this->getUserActiveService()->getActiveUser(3);
        $this->assertEquals(array('id' => 2, 'userId' => 3), $result);
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
        $result = $this->getUserActiveService()->isActiveUser();
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

    public function testWriteToFile()
    {
        $result = $this->getUserActiveService()->writeToFile(__DIR__.'/File/test.txt', 2);
        $this->assertTrue($result);
    }

    protected function getUserActiveService()
    {
        return $this->createService('User:UserActiveService');
    }
}
