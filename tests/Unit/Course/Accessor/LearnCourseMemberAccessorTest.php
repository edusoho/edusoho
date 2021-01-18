<?php

namespace Tests\Unit\Course\Accessor;

use Biz\BaseTestCase;
use Biz\Course\Accessor\LearnCourseMemberAccessor;
use Biz\User\CurrentUser;

class LearnCourseMemberAccessorTest extends BaseTestCase
{
    public function testAccess()
    {
        $accessor = new LearnCourseMemberAccessor($this->getBiz());
        $result = $accessor->access([]);
        $this->assertEquals('member.not_found', $result['code']);

        $currentUser = new CurrentUser();
        $currentUser->fromArray([
            'id' => 0,
            'nickname' => 'admin1',
            'email' => 'admin3@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => ['ROLE_USER', 'ROLE_ADMIN'],
        ]);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $result = $accessor->access([]);
        $this->assertEquals('user.not_login', $result['code']);
    }

    public function testAccessWithLockedUser()
    {
        $currentUser = new CurrentUser();
        $currentUser->fromArray([
            'id' => 2,
            'nickname' => 'admin1',
            'email' => 'admin3@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => ['ROLE_USER', 'ROLE_ADMIN'],
            'locked' => 1,
        ]);
        $accessor = new LearnCourseMemberAccessor($this->getBiz());
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $result = $accessor->access([]);
        $this->assertEquals('user.locked', $result['code']);
    }

    public function testAccessWithEmptyMember()
    {
        $currentUser = new CurrentUser();
        $currentUser->fromArray([
            'id' => 2,
            'nickname' => 'admin1',
            'email' => 'admin3@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => ['ROLE_USER', 'ROLE_ADMIN'],
            'locked' => 0,
        ]);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $accessor = new LearnCourseMemberAccessor($this->getBiz());
        $this->mockBiz(
            'Course:MemberService',
            [
                [
                    'functionName' => 'getCourseMember',
                    'returnValue' => [],
                    'withParams' => [111, 2],
                ],
            ]
        );
        $result = $accessor->access(['id' => 111]);
        $this->assertEquals('member.not_found', $result['code']);
    }

    public function testAccessWithExistedMember()
    {
        $currentUser = new CurrentUser();
        $currentUser->fromArray([
            'id' => 2,
            'nickname' => 'admin1',
            'email' => 'admin3@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => ['ROLE_USER', 'ROLE_ADMIN'],
            'locked' => 0,
        ]);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $accessor = new LearnCourseMemberAccessor($this->getBiz());
        $this->mockBiz(
            'Course:MemberService',
            [
                [
                    'functionName' => 'getCourseMember',
                    'returnValue' => ['deadline' => 1000],
                    'withParams' => [111, 2],
                    'runTimes' => 1,
                ],
                [
                    'functionName' => 'getCourseMember',
                    'returnValue' => ['deadline' => 0],
                    'withParams' => [111, 2],
                    'runTimes' => 1,
                ],
            ]
        );
        $result = $accessor->access(['id' => 111]);
        $this->assertEquals('member.expired', $result['code']);

        $result = $accessor->access(['id' => 111]);
        $this->assertNull($result);
    }
}
