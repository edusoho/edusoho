<?php

namespace Tests\Unit\ItemBankExercis\Member;

use Biz\BaseTestCase;
use Biz\ItemBankExercise\Member\StudentMember;
use Biz\Role\Util\PermissionBuilder;
use Biz\User\CurrentUser;

class StudentMemberTest extends BaseTestCase
{
    public function testJoin()
    {
        $this->mockBiz('ItemBankExercise:ExerciseService', [
            [
                'functionName' => 'get',
                'runTimes' => 2,
                'withParams' => [1],
                'returnValue' => [
                    'id' => 1,
                    'title' => 'test',
                    'questionBankId' => 1,
                    'status' => 'published',
                    'expiryMode' => 'forever',
                    'expiryDays' => 0,
                ],
            ],
        ]);
        $this->mockBiz('ItemBankExercise:ExerciseMemberDao', [
            [
                'functionName' => 'create',
                'runTimes' => 1,
                'withParams' => [
                    [
                        'exerciseId' => 1,
                        'questionBankId' => 1,
                        'userId' => 3,
                        'deadline' => 0,
                        'role' => 'student',
                        'remark' => 'test',
                        'orderId' => 0,
                    ],
                ],
                'returnValue' => [
                    'id' => 5,
                    'exerciseId' => 1,
                    'userId' => 3,
                    'role' => 'student',
                    'orderId' => 0,
                ],
            ],
            [
                'functionName' => 'getByExerciseIdAndUserIdAndRole',
                'runTimes' => 1,
                'withParams' => [1, 3, 'student'],
                'returnValue' => [],
            ],
        ]);
        $currentUser = new CurrentUser();
        $currentUser->fromArray([
            'id' => 3,
            'nickname' => 'admin3',
            'email' => 'admin3@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => ['ROLE_USER', 'ROLE_SUPER_ADMIN'],
        ]);
        $currentUser->setPermissions(PermissionBuilder::instance()->getPermissionsByRoles($currentUser->getRoles()));
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $studentMember = new StudentMember($this->biz);
        $res = $studentMember->join(1, $currentUser->getId(), ['remark' => 'test', 'reason' => 'test', 'reasonType' => 'test']);
        $this->assertEquals(5, $res['id']);
    }
}
