<?php

namespace Tests\Unit\ItemBankExercise\Accessor;

use Biz\BaseTestCase;
use Biz\ItemBankExercise\Accessor\LearnExerciseMemberAccessor;
use Biz\User\CurrentUser;

class LearnExerciseMemberAccessorTest extends BaseTestCase
{
    public function testAccess()
    {
        $this->mockBiz(
            'ItemBankExercise:ExerciseMemberService',
            [
                [
                    'functionName' => 'getExerciseMember',
                    'returnValue' => [
                        'deadline' => 0,
                    ],
                ],
            ]
        );

        $accessor = new LearnExerciseMemberAccessor($this->getBiz());
        $result = $accessor->access([
            'id' => 1,
            'status' => 'published',
            'expiryMode' => 'forever',
            'joinEnable' => 1,
        ]);

        $this->assertNull($result);
    }

    public function testAccess_whenUserNotLogin_thenReturnError()
    {
        $currentUser = new CurrentUser();
        $currentUser->fromArray([]);
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $accessor = new LearnExerciseMemberAccessor($this->getBiz());
        $result = $accessor->access([
            'id' => 1,
            'status' => 'published',
            'expiryMode' => 'forever',
            'joinEnable' => 1,
        ]);

        $this->assertEquals($result['code'], 'user.not_login');
    }

    public function testAccess_whenUserLocked_thenReturnError()
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
            ]
        );
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $accessor = new LearnExerciseMemberAccessor($this->getBiz());
        $result = $accessor->access([
            'id' => 1,
            'status' => 'published',
            'expiryMode' => 'forever',
            'joinEnable' => 1,
        ]);

        $this->assertEquals($result['code'], 'user.locked');
    }

    public function testAccess_whenMemberNotFound_thenReturnError()
    {
        $accessor = new LearnExerciseMemberAccessor($this->getBiz());
        $result = $accessor->access([
            'id' => 1,
            'status' => 'published',
            'expiryMode' => 'forever',
            'joinEnable' => 1,
        ]);

        $this->assertEquals($result['code'], 'member.not_found');
    }

    public function testAccess_whenDeadlineExpired_thenReturnError()
    {
        $this->mockBiz(
            'ItemBankExercise:ExerciseMemberService',
            [
                [
                    'functionName' => 'getExerciseMember',
                    'returnValue' => [
                        'deadline' => 1,
                    ],
                ],
            ]
        );

        $accessor = new LearnExerciseMemberAccessor($this->getBiz());
        $result = $accessor->access([
            'id' => 1,
            'status' => 'published',
            'expiryMode' => 'forever',
            'joinEnable' => 1,
        ]);

        $this->assertEquals($result['code'], 'member.expired');
    }
}
