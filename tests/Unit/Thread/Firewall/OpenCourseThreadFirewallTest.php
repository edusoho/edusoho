<?php

namespace Tests\Unit\Thread\Firewall;

use Biz\BaseTestCase;
use Biz\Thread\Firewall\OpenCourseThreadFirewall;
use Biz\User\CurrentUser;

class OpenCourseThreadFirewallTest extends BaseTestCase
{
    public function testAccessPostCreate()
    {
        $firewall = new OpenCourseThreadFirewall();
        $this->assertTrue($firewall->accessPostCreate(array()));
    }

    public function testAccessPostCreateWithoutLogin()
    {
        $this->biz['user'] = new CurrentUser();
        $firewall = new OpenCourseThreadFirewall();
        $this->assertFalse($firewall->accessPostCreate(array()));
    }

    public function testAccessPostVote()
    {
        $firewall = new OpenCourseThreadFirewall();
        $this->assertTrue($firewall->accessPostVote(array()));
    }

    public function testAccessPostVoteWithoutLogin()
    {
        $this->biz['user'] = new CurrentUser();
        $firewall = new OpenCourseThreadFirewall();
        $this->assertFalse($firewall->accessPostVote(array()));
    }

    public function testAccessPostDelete()
    {
        $firewall = new OpenCourseThreadFirewall();
        $currentUser = $this->getCurrentUser();
        $this->mockBiz(
            'Thread:ThreadService',
            array(
                array(
                    'functionName' => 'getPost',
                    'withParams' => array(123),
                    'returnValue' => array('userId' => $currentUser['id']),
                ),
            )
        );
        $this->assertTrue($firewall->accessPostDelete(array('id' => 123)));
    }

    public function testAccessPostDeleteWithoutLogin()
    {
        $this->biz['user'] = new CurrentUser();
        $firewall = new OpenCourseThreadFirewall();
        $this->assertFalse($firewall->accessPostDelete(array()));
    }
}
