<?php

namespace Tests\Unit\Component\OAuthServer\Storage;

use Biz\BaseTestCase;
use AppBundle\Component\OAuthServer\Storage\UserCredentials;

class UserCredentialsTest extends BaseTestCase
{
    public function testCheckUserCredentials()
    {
        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $credentials = new UserCredentials(self::$appKernel->getContainer());
        $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'getUserByLoginField',
                    'returnValue' => array('id' => 2, 'nickname' => 'username'),
                    'withParams' => array('username'),
                ),
                array(
                    'functionName' => 'verifyPassword',
                    'returnValue' => true,
                    'withParams' => array(2, 'password'),
                ),
            )
        );
        $result = $credentials->checkUserCredentials('username', 'password');
        $this->assertTrue($result);
    }

    public function testGetUserDetails()
    {
        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $credentials = new UserCredentials(self::$appKernel->getContainer());
        $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'getUserByLoginField',
                    'returnValue' => array('id' => 2, 'nickname' => 'username'),
                    'withParams' => array('username'),
                ),
            )
        );
        $result = $credentials->getUserDetails('username');
        $this->assertEquals(array('user_id' => 2, 'scope' => 'default'), $result);
    }
}
