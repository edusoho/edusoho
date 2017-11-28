<?php

namespace Tests\Unit\Thread;

use Biz\BaseTestCase;
use Biz\User\CurrentUser;
use Biz\Thread\Firewall\ArticleThreadFirewall;

class ArticleThreadFirewallTest extends BaseTestCase
{
    public function testaccessPostCreate()
    {
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 2,
            'nickname' => 'admin1',
            'email' => 'admin3@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER', 'ROLE_ADMIN'),
        ));
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $fireWall = new ArticleThreadFirewall();
        $result1 = $fireWall->accessPostCreate('');
        $this->assertTrue($result1);

        $currentUser->__set('id', 0);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $result2 = $fireWall->accessPostCreate('');
        $this->assertFalse($result2);
    }

    public function testaccessPostDelete()
    {
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 2,
            'nickname' => 'admin1',
            'email' => 'admin3@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER', 'ROLE_ADMIN'),
        ));
        $currentUser->setPermissions(array('admin' => 1));
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
        $articleThreadFirewall = new ArticleThreadFirewall();
        $result1 = $articleThreadFirewall->accessPostDelete(array('id' => 111));
        $currentUser->__set('id', 0);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $result2 = $articleThreadFirewall->accessPostDelete(array('id' => 111));

        $this->assertTrue($result1);
        $this->assertFalse($result2);
    }

    public function testaccessPostVote()
    {
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 2,
            'nickname' => 'admin1',
            'email' => 'admin3@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER', 'ROLE_ADMIN'),
        ));
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $fireWall = new ArticleThreadFirewall();
        $result1 = $fireWall->accessPostVote('');
        $this->assertTrue($result1);

        $currentUser->__set('id', 0);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $result2 = $fireWall->accessPostVote('');
        $this->assertFalse($result2);
    }

    public function getCurrentUser()
    {
        return $this->getServiceKernel()->getCurrentUser();
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    protected function getThreadDao()
    {
        return $this->createDao('Thread:ThreadDao');
    }
}
