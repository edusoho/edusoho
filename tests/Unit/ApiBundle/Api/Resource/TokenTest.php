<?php

namespace Tests\Unit\ApiBundle\Api\Resource;

use ApiBundle\Api\ResourceKernel;
use ApiBundle\ApiTestCase;
use Symfony\Component\HttpFoundation\Request;

class TokenTest extends ApiTestCase
{
    /**
     * @expectedException \ApiBundle\Api\Exception\ResourceNotFoundException
     */
    public function testAddWithUserNameError()
    {
        $kernel = new ResourceKernel(
            $this->getContainer()
        );
        $kernel->handle(Request::create('http://test.com/tokens', 'POST', array('username' => 'xxx')));
    }

    /**
     * @expectedException \ApiBundle\Api\Exception\InvalidArgumentException
     */
    public function testAddWithPasswordError()
    {
        $kernel = new ResourceKernel(
            $this->getContainer()
        );
        $kernel->handle(Request::create('http://test.com/tokens', 'POST', array('username' => 'admin@admin.com', 'password' => 'xxx')));
    }

    /**
     * @expectedException \ApiBundle\Api\Exception\BannedCredentialException
     */
    public function testUserLocked()
    {
        $fakeUser = array(
            'id' => 1,
            'username' => 'user',
            'password' => 'blablabla...',
            'locked' => 1,
        );

        $this->mockBiz('User:UserService', array(
            array('functionName' => 'getUserByLoginField', 'runTimes' => 1, 'returnValue' => $fakeUser),
            array('functionName' => 'verifyPassword', 'runTimes' => 1, 'returnValue' => 1),
            array('functionName' => 'getToken', 'runTimes' => 1, 'returnValue' => 1),
            array('functionName' => 'getUser', 'runTimes' => 1, 'returnValue' => $this->getCurrentUser()->toArray()),
        ));
        $kernel = new ResourceKernel(
            $this->getContainer()
        );
        $kernel->handle(Request::create('http://test.com/tokens', 'POST', array('username' => 'admin@admin.com', 'password' => 'admin')));
    }

    public function testAddWithSuccess()
    {
        $this->mockBiz('VipPlugin:Vip:VipService', array(
            array('functionName' => 'getMemberByUserId', 'returnValue' => array('levelId' => 1, 'deadline' => 1))
        ));
        $this->mockBiz('VipPlugin:Vip:LevelService', array(
            array('functionName' => 'getLevel', 'returnValue' => array('name' => 1, 'seq' => 1))
        ));

        $kernel = new ResourceKernel(
            $this->getContainer()
        );
        $token = $kernel->handle(Request::create('http://test.com/tokens', 'POST', array('username' => 'admin@admin.com', 'password' => 'admin')));
        $this->assertArrayHasKey('token', $token);
        $user = $this->getCurrentUser();
        $this->assertEquals($user['id'], $token['user']['id']);
    }
}
