<?php

namespace Tests\Unit\Course\Accessor;

use Biz\BaseTestCase;
use Biz\User\CurrentUser;
use Biz\Course\Accessor\LearnCourseMemberAccessor;

class LearnCourseMemberAccessorTest extends BaseTestCase
{
    public function testAccess()
    {
        $accessor = new LearnCourseMemberAccessor($this->getBiz());
        $result = $accessor->access(array());
        $this->assertNull($result);

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
        $result = $accessor->access(array());
        $this->assertEquals('UN_LOGIN', $result['code']);
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
        $accessor = new LearnCourseMemberAccessor($this->getBiz());
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $result = $accessor->access(array());
        $this->assertEquals('LOCKED_USER', $result['code']);
    }

    public function testAccessWithEmptyMember()
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
        $accessor = new LearnCourseMemberAccessor($this->getBiz());
        $this->mockBiz(
            'Course:MemberService',
            array(
                array(
                    'functionName' => 'getCourseMember',
                    'returnValue' => array(),
                    'withParams' => array(111, 2),
                ),
            )
        );
        $result = $accessor->access(array('id' => 111));
        $this->assertEquals('NOTFOUND_MEMBER', $result['code']);
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
        $accessor = new LearnCourseMemberAccessor($this->getBiz());
        $this->mockBiz(
            'Course:MemberService',
            array(
                array(
                    'functionName' => 'getCourseMember',
                    'returnValue' => array('deadline' => 1000),
                    'withParams' => array(111, 2),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'getCourseMember',
                    'returnValue' => array('deadline' => 0),
                    'withParams' => array(111, 2),
                    'runTimes' => 1,
                ),
            )
        );
        $result = $accessor->access(array('id' => 111));
        $this->assertEquals('EXPIRED_MEMBER', $result['code']);

        $result = $accessor->access(array('id' => 111));
        $this->assertNull($result);
    }
}
