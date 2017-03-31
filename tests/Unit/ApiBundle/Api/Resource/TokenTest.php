<?php

namespace Tests\Unit\ApiBundle\Api\Resource;

use ApiBundle\Api\PathParser;
use ApiBundle\Api\Resource\ResourceManager;
use ApiBundle\Api\ResourceKernel;
use ApiBundle\ApiTestCase;
use Biz\BaseTestCase;
use Symfony\Component\HttpFoundation\Request;

class TokenTest extends ApiTestCase
{
    /**
     * @expectedException ApiBundle\Api\Exception\ResourceNotFoundException
     */
    public function testAddWithUserNameError()
    {
        $kernel = new ResourceKernel(
            new PathParser(),
            new ResourceManager($this->getBiz())
        );
        $kernel->handle(Request::create('http://test.com/tokens', 'POST', array('username' => 'xxx')));
    }

    /**
     * @expectedException ApiBundle\Api\Exception\InvalidArgumentException
     */
    public function testAddWithPasswordError()
    {
        $kernel = new ResourceKernel(
            new PathParser(),
            new ResourceManager($this->getBiz())
        );
        $kernel->handle(Request::create('http://test.com/tokens', 'POST', array('username' => 'admin@admin.com', 'password' => 'xxx')));
    }

    /**
     * @expectedException ApiBundle\Api\Exception\BannedCredentialException
     */
    public function testUserLocked()
    {

        $fakeUser = array(
            'id' => 1,
            'username' => 'user',
            'password' => 'blablabla...',
            'locked' => 1
        );

        $this->mockBiz('User:UserService',array(
            array('functionName' => 'getUserByLoginField', 'runTimes' => 1, 'returnValue' => $fakeUser),
            array('functionName' => 'verifyPassword', 'runTimes' => 1, 'returnValue' => 1),
        ));
        $kernel = new ResourceKernel(
            new PathParser(),
            new ResourceManager($this->getBiz())
        );
        $kernel->handle(Request::create('http://test.com/tokens', 'POST', array('username' => 'admin@admin.com', 'password' => 'zpeh2fmD8mhGqcdP')));
    }

    public function testAddWithSuccess()
    {
        $kernel = new ResourceKernel(
            new PathParser(),
            new ResourceManager($this->getBiz())
        );
        $token = $kernel->handle(Request::create('http://test.com/tokens', 'POST', array('username' => 'admin@admin.com', 'password' => 'zpeh2fmD8mhGqcdP')));
        $this->assertArrayHasKey('token', $token);
        $user = $this->getCurrentUser();
        $this->assertEquals($user['id'], $token['userId']);
    }
}