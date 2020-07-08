<?php

namespace Tests\Unit\ItemBankExercise\Service;

use Biz\BaseTestCase;
use Biz\ItemBankExercise\Dao\ExerciseMemberDao;
use Biz\ItemBankExercise\Service\ExerciseMemberService;
use Biz\ItemBankExercise\Service\ExerciseService;
use Biz\Role\Util\PermissionBuilder;
use Biz\User\CurrentUser;
use Biz\User\Service\UserService;

class ExerciseMemberServiceTest extends BaseTestCase
{
    public function testCount()
    {
        $this->batchCreateExerciseMembers();

        $res = $this->getExerciseMemberService()->count([
            'exerciseId' => 1,
            'role' => 'student',
        ]);

        $this->assertEquals('1', count($res));
    }

    public function testUpdate()
    {
        $member = $this->createExerciseMember();
        $res = $this->getExerciseMemberService()->update(
            $member['id'],
            [
                'doneQuestionNum' => 1,
                'rightQuestionNum' => 1,
                'completionRate' => 0.1,
                'masteryRate' => 0.1,
            ]
        );
        $this->assertEquals(1, $res['doneQuestionNum']);
        $this->assertEquals(1, $res['rightQuestionNum']);
        $this->assertEquals(0.1, $res['completionRate']);
        $this->assertEquals(0.1, $res['masteryRate']);
    }

    public function testGetByExerciseIdAndUserId()
    {
        $member = $this->createExerciseMember();
        $res = $this->getExerciseMemberService()->getByEerciseIdAndUserId(1, 1);
        $this->assertEquals($member['exerciseId'], $res['exerciseId']);
        $this->assertEquals($member['userId'], $res['userId']);
    }

    public function testSearch()
    {
        $this->batchCreateExerciseMembers();
        $res = $this->getExerciseMemberService()->search(
            [
                'exerciseId' => 1,
                'role' => 'student',
            ],
            null,
            0,
            1
        );
        $this->assertEquals(1, count($res));
        $this->assertEquals(1, $res[0]['exerciseId']);
        $this->assertEquals('student', $res[0]['role']);
    }

    public function testIsExerciseMember()
    {
        $user = $this->createNormalUser();
        $exercise = $this->createExercise();
        $this->getExerciseMemberDao()->create(
            [
                'exerciseId' => $exercise['id'],
                'questionBankId' => 1,
                'userId' => $user['id'],
                'role' => 'student',
                'remark' => 'aaa',
            ]
        );
        $result = $this->getExerciseMemberService()->isExerciseMember($exercise['id'], $user['id']);
        $this->assertEquals(true, $result);
    }

    public function testBecomeStudent()
    {
        $user = $this->createNormalUser();
        $exercise = $this->getExerciseService()->create(
            [
                'id' => 1,
                'title' => 'test',
                'questionBankId' => 1,
                'categoryId' => 1,
                'seq' => 1,
                'status' => 'published',
                'expiryMode' => 'forever',
            ]
        );

        $result = $this->getExerciseMemberService()->isExerciseMember($exercise['id'], $user['id']);
        $this->assertEquals(false, $result);

        $this->getExerciseMemberService()->becomeStudent($exercise['id'], $user['id'], ['remark' => '123']);
        $result = $this->getExerciseMemberService()->isExerciseMember($exercise['id'], $user['id']);
        $this->assertEquals(true, $result);
    }

    public function testAddTeacher()
    {
        $exercise = $this->createExercise();
        $res = $this->getExerciseService()->isExerciseTeacher($exercise['id'], 2);
        $this->assertEquals(false, $res);
        $currentUser = new CurrentUser();
        $currentUser->fromArray([
            'id' => 2,
            'nickname' => 'admin3',
            'email' => 'admin3@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => ['ROLE_USER', 'ROLE_SUPER_ADMIN'],
        ]);
        $currentUser->setPermissions(PermissionBuilder::instance()->getPermissionsByRoles($currentUser->getRoles()));
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $this->getExerciseMemberService()->addTeacher($exercise['id']);
        $res = $this->getExerciseService()->isExerciseTeacher($exercise['id'], 2);
        $this->assertEquals(true, $res);
    }

    public function testGetExerciseMember()
    {
        $member = $this->createExerciseMember();
        $res = $this->getExerciseMemberService()->getExerciseMember($member['exerciseId'], $member['userId']);
        $this->assertEquals($member['exerciseId'], $res['exerciseId']);
        $this->assertEquals($member['userId'], $res['userId']);
    }

    public function testRemarkStudent()
    {
        $member = $this->createExerciseMember();
        $res = $this->getExerciseMemberService()->remarkStudent($member['exerciseId'], $member['userId'], 'remark');
        $this->assertEquals('remark', $res['remark']);
    }

    public function testBatchUpdateMemberDeadlines()
    {
        $user = $this->createNormalUser();
        $exercise = $this->createExercise();
        $member = $this->getExerciseMemberDao()->create(
            [
                'exerciseId' => $exercise['id'],
                'questionBankId' => 1,
                'userId' => $user['id'],
                'role' => 'student',
                'remark' => 'aaa',
            ]
        );

        $this->getExerciseMemberService()->batchUpdateMemberDeadlines($exercise['id'], [0 => $user['id']], ['updateType' => 'deadline', 'deadline' => time()]);
        $result = $this->getExerciseMemberService()->getExerciseMember($exercise['id'], $user['id']);
        $this->assertEquals(time(), (int) $result['deadline']);
    }

    private function createExercise()
    {
        return $this->getExerciseService()->create(
            [
                'id' => 1,
                'title' => 'test',
                'questionBankId' => 1,
                'categoryId' => 1,
                'seq' => 1,
                'expiryMode' => 'forever',
            ]
        );
    }

    private function createNormalUser()
    {
        return $this->getUserService()->register([
            'id' => 1,
            'email' => 'normal@user.com',
            'nickname' => 'normal',
            'password' => 'user123',
            'currentIp' => '127.0.0.1',
            'roles' => ['ROLE_USER'],
        ]);
    }

    protected function createExerciseMember()
    {
        return $this->getExerciseMemberDao()->create(
            [
                'exerciseId' => 1,
                'questionBankId' => 1,
                'userId' => 1,
                'role' => 'student',
                'remark' => 'aaa',
            ]
        );
    }

    protected function batchCreateExerciseMembers()
    {
        return $this->getExerciseMemberDao()->batchCreate(
            [
                [
                    'exerciseId' => 1,
                    'questionBankId' => 1,
                    'userId' => 1,
                    'role' => 'teacher',
                    'remark' => 'aaa',
                ],
                [
                    'exerciseId' => 1,
                    'questionBankId' => 1,
                    'userId' => 2,
                    'role' => 'student',
                    'remark' => 'bbb',
                ],
                [
                    'exerciseId' => 2,
                    'questionBankId' => 2,
                    'userId' => 3,
                    'role' => 'student',
                    'remark' => 'ccc',
                ],
            ]
        );
    }

    /**
     * @return ExerciseMemberDao
     */
    protected function getExerciseMemberDao()
    {
        return $this->createDao('ItemBankExercise:ExerciseMemberDao');
    }

    /**
     * @return ExerciseMemberService
     */
    protected function getExerciseMemberService()
    {
        return $this->createService('ItemBankExercise:ExerciseMemberService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return ExerciseService
     */
    protected function getExerciseService()
    {
        return $this->createService('ItemBankExercise:ExerciseService');
    }
}
