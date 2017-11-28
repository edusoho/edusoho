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
            'id' => 0,
            'nickname' => 'admin1',
            'email' => 'admin3@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER', 'ROLE_ADMIN'),
        ));
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $accessor = new JoinCourseMemberAccessor($this->getBiz());
        $result1 = $accessor->access(array());
        $this->assertEquals('user.not_login', $result1['code']);

        $currentUser->__set('locked', 1);
        $currentUser->__set('id', 2);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $result2 = $accessor->access(array());
        $this->assertEquals('user.locked', $result2['code']);

        $currentUser->__set('locked', 0);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $this->mockBiz(
            'Course:MemberService',
            array(
                array(
                    'functionName' => 'getCourseMember',
                    'returnValue' => 1,
                    'withParams' => array(111, 2),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'getCourseMember',
                    'returnValue' => 0,
                    'withParams' => array(111, 2),
                    'runTimes' => 1,
                ),
            )
        );
        $result3 = $accessor->access(array('id' => 111));
        $this->assertEquals('member.member_exist', $result3['code']);

        $result4 = $accessor->access(array('id' => 111));
        $this->assertNull($result4);
    }
}