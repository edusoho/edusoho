<?php

namespace Tests\Unit\Thread;

use Biz\BaseTestCase;
use Biz\User\CurrentUser;
use Biz\Thread\Firewall\ArticleThreadFirewall;

class ArticleThreadFirewallTest extends BaseTestCase
{
    public function testaccessPostCreate()
    {
        $user = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $fireWall = new ArticleThreadFirewall();
        $result1 = $fireWall->accessPostCreate('');
        $this->assertTrue($result1);

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
        $result2 = $fireWall->accessPostCreate('');
        $this->assertFalse($result2);
    }

    public function testaccessPostDelete()
    {
        $user = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $this->mockBiz(
            'Thread:ThreadService',
            array(
                array(
                    'functionName' => 'getPost',
                    'returnValue' => array('id' => 111, 'userId' => 2),
                    'withParams' => array(111),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'getPost',
                    'returnValue' => array('id' => 111, 'userId' => 3),
                    'withParams' => array(111),
                    'runTimes' => 1,
                ),
            )
        );
        $fireWall = new ArticleThreadFirewall();
        $result1 = $fireWall->accessPostDelete(array('id' => 111));
        $currentUser->__set('id', 0);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $result2 = $fireWall->accessPostDelete(array('id' => 111));

        $this->assertTrue($result1);
        $this->assertFalse($result2);
    }

    public function testaccessPostVote()
    {
        $user = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $fireWall = new ArticleThreadFirewall();
        $result1 = $fireWall->accessPostVote('');
        $this->assertTrue($result1);

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
        $result2 = $fireWall->accessPostVote('');
        $this->assertFalse($result2);
    }

    protected function createUser()
    {
        $user = array();
        $user['email'] = 'user@user.com';
        $user['nickname'] = 'user';
        $user['password'] = 'user';
        $user = $this->getUserService()->register($user);
        $user['currentIp'] = '127.0.0.1';
        $user['roles'] = array('ROLE_USER', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER');

        return $user;
    }

    public function getCurrentUser()
    {
        return $this->getServiceKernel()->getCurrentUser();
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }
}
