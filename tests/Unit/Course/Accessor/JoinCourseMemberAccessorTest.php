<?php

namespace Tests\Unit\Course\Accessor;

use Biz\BaseTestCase;
use Biz\User\CurrentUser;
use Biz\Course\Accessor\JoinCourseMemberAccessor;

class JoinCourseMemberAccessorTest extends BaseTestCase
{
    public function testAccess()
    {
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 2,
            'nickname' => 'admin1',
            'email' => 'admin3@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER', 'ROLE_ADMIN'),
            'locked' => 0,
        ));
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $accessor = new JoinCourseMemberAccessor($this->getBiz());
        $this->mockBiz(
            'Course:MemberService',
            array(
                array(
                    'functionName' => 'getCourseMember',
                    'returnValue' => 0,
                    'withParams' => array(111, 2),
                ),
            )
        );

        $result = $accessor->access(array('id' => 111));
        $this->assertNull($result);
    }

    public function testAccessWithNotLoginUser()
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
        $accessor = new JoinCourseMemberAccessor($this->getBiz());

        $result = $accessor->access(array());
        $this->assertEquals('user.not_login', $result['code']);
    }

    public function testAccessWithLockedUser()
    {
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 2,
            'nickname' => 'admin1',
            'email' => 'admin3@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER', 'ROLE_ADMIN'),
            'locked' => 1,
        ));
        $accessor = new JoinCourseMemberAccessor($this->getBiz());
        $this->getServiceKernel()->setCurrentUser($currentUser);
        
        $result = $accessor->access(array());
        $this->assertEquals('user.locked', $result['code']);
    }

    public function testAccessWithExistedMember()
    {
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 2,
            'nickname' => 'admin1',
            'email' => 'admin3@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER', 'ROLE_ADMIN'),
            'locked' => 0,
        ));
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $accessor = new JoinCourseMemberAccessor($this->getBiz());
        $this->mockBiz(
            'Course:MemberService',
            array(
                array(
                    'functionName' => 'getCourseMember',
                    'returnValue' => 1,
                    'withParams' => array(111, 2),
                ),
            )
        );
        $result = $accessor->access(array('id' => 111));
        $this->assertEquals('member.member_exist', $result['code']);
    }
}
