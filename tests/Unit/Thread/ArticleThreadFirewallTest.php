<?php

namespace Tests\Unit\Thread;

use Biz\BaseTestCase;
use Biz\User\CurrentUser;
use Biz\Thread\Firewall\ArticleThreadFirewall;

class ArticleThreadFirewallTest extends BaseTestCase
{
    public function testAccessPostCreate()
    {
        $fireWall = new ArticleThreadFirewall();
        $result = $fireWall->accessPostCreate('');
        $this->assertTrue($result);
    }

    public function testAccessPostCreateWithNotLoginUser()
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
        $fireWall = new ArticleThreadFirewall();
        $result = $fireWall->accessPostCreate('');
        $this->assertFalse($result);
    }

    public function testAccessPostDelete()
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
        $articleThreadFirewall = new ArticleThreadFirewall();
        $result = $articleThreadFirewall->accessPostDelete(array('id' => 111));
        $this->assertFalse($result);
    }

    public function testAccessPostDeleteWithPostCreater()
    {
        $this->mockBiz(
            'Thread:ThreadService',
            array(
                array(
                    'functionName' => 'getPost',
                    'returnValue' => array('id' => 111, 'userId' => 1),
                    'withParams' => array(111),
                ),
            )
        );
        $articleThreadFirewall = new ArticleThreadFirewall();
        $result = $articleThreadFirewall->accessPostDelete(array('id' => 111));
        $this->assertTrue($result);
    }

    public function testAccessPostVote()
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
        $fireWall = new ArticleThreadFirewall();
        $result = $fireWall->accessPostVote('');
        $this->assertFalse($result);
    }

    public function testAccessPostVoteWithLoginUser()
    {
        $fireWall = new ArticleThreadFirewall();
        $result = $fireWall->accessPostVote('');
        $this->assertTrue($result);
    }
}
