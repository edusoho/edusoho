<?php

namespace Tests\Unit\ItemBankExercise\Service;

use Biz\BaseTestCase;
use Biz\ItemBankExercise\Dao\ExerciseDao;
use Biz\ItemBankExercise\Service\ExerciseService;
use Biz\Role\Util\PermissionBuilder;
use Biz\User\CurrentUser;
use Biz\User\Service\UserService;

class ExerciseServiceTest extends BaseTestCase
{
    public function testUpdate()
    {
        $this->createExercise();

        $res = $this->getExerciseService()->update(
            1,
            [
                'title' => 'test1',
                'categoryId' => 2,
            ]
        );

        $this->assertEquals('test1', $res['title']);
        $this->assertEquals(2, $res['categoryId']);
    }

    public function testCreate()
    {
        $res = $this->createExercise();

        $this->assertEquals('test', $res['title']);
        $this->assertEquals(1, $res['questionBankId']);
        $this->assertEquals(1, $res['categoryId']);
        $this->assertEquals(1, $res['seq']);
    }

    public function testGet()
    {
        $this->createExercise();

        $res = $this->getExerciseService()->get(1);

        $this->assertEquals(1, $res['id']);
        $this->assertEquals(1, $res['questionBankId']);
    }

    public function testCount()
    {
        $this->batchCreateExercise();

        $res = $this->getExerciseService()->count([
            'questionBankId' => 1,
        ]);

        $this->assertEquals('1', count($res));
    }

    public function testFindByIds()
    {
        $excepted = $this->batchCreateExercise();
        $res = $this->getExerciseService()->findByIds([1, 2, 3]);
        $this->assertEquals(1, $res[1]['id']);
        $this->assertEquals(2, $res[2]['id']);
        $this->assertEquals(3, $res[3]['id']);
    }

    public function testSearch()
    {
        $this->batchCreateExercise();
        $res = $this->getExerciseService()->search([], ['seq' => 'DESC'], 0, 2);
        $this->assertEquals('2', count($res));
        $this->assertEquals(3, $res[0]['seq']);
    }

    /**
     * @expectedException \Biz\User\UserException
     * @expectedExceptionMessage exception.user.unlogin
     */
    public function testTryManageCourseSetUnLogin()
    {
        $currentUser = new CurrentUser();
        $currentUser->fromArray([
            'id' => 0,
            'nickname' => '游客',
            'currentIp' => '127.0.0.1',
            'roles' => ['ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER'],
            'org' => ['id' => 1],
        ]);

        $this->getServiceKernel()->setBiz($this->getBiz());
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $this->getExerciseService()->tryManageExercise(1);
    }

    /**
     * @expectedException \Biz\ItemBankExercise\ItemBankExerciseException
     * @expectedExceptionMessage exception.item_bank_exercise.exercise.not_found
     */
    public function testTryManageCourseSetNotFoundException()
    {
        $this->getExerciseService()->tryManageExercise(1);
    }

    public function testUpdateExerciseStatistics()
    {
        $this->createExercise();
        $res = $this->getExerciseService()->updateExerciseStatistics(1, ['studentNum']);
        $this->assertEquals(0, $res['studentNum']);
    }

    public function testHasExerciseManageRoleUnLogin()
    {
        $this->createExercise();

        $currentUser = new CurrentUser();
        $currentUser->fromArray([
            'id' => 0,
            'nickname' => '游客',
            'currentIp' => '127.0.0.1',
            'roles' => ['ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER'],
            'org' => ['id' => 1],
        ]);

        $this->getServiceKernel()->setBiz($this->getBiz());
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $this->assertFalse($this->getExerciseService()->hasExerciseManagerRole(1));
    }

    public function testHasExerciseManageRoleFalse()
    {
        $this->createExercise();

        $user = $this->getUserService()->register([
            'nickname' => 'user',
            'email' => 'user@user.com',
            'password' => 'user123',
            'createdIp' => '127.0.0.1',
            'orgCode' => '1.',
            'orgId' => '1',
        ]);

        $user['currentIp'] = $user['createdIp'];
        $user['org'] = ['id' => 1];
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->grantPermissionToUser($currentUser);
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $this->assertTrue($this->getExerciseService()->hasExerciseManagerRole(3231));
        $this->assertTrue($this->getExerciseService()->hasExerciseManagerRole(1));
    }

    public function testHasExerciseManageRole()
    {
        $exercise = $this->createExercise();

        $result = $this->getExerciseService()->hasExerciseManagerRole();
        $this->assertTrue($result);
        $result = $this->getExerciseService()->hasExerciseManagerRole(2332);
        $this->assertTrue($result);
        $result = $this->getExerciseService()->hasExerciseManagerRole($exercise['id']);
        $this->assertTrue($result);
    }

    public function testIsExerciseTeacher()
    {
        $exercise = $this->getExerciseService()->create(
            [
                'title' => 'test',
                'questionBankId' => 1,
                'categoryId' => 1,
                'seq' => 1,
                'teacherIds' => [1],
            ]
        );
        $res = $this->getExerciseService()->isExerciseTeacher($exercise['id'], 1);
        $this->assertTrue($res);
    }

    public function testChangeExerciseCover()
    {
        $exercise = $this->createExercise();
        $updated = $this->getExerciseService()->changeExerciseCover($exercise['id'], [
            'large' => 1,
            'middle' => 2,
            'small' => 3,
        ]);
        $this->assertNotEmpty($updated['cover']);
    }

    public function testGetByQuestionBankId()
    {
        $excepted = $this->createExercise();
        $res = $this->getExerciseService()->getByQuestionBankId(1);
        $this->assertEquals($excepted['questionBankId'], $res['questionBankId']);
    }

    public function testupdateModuleEnable()
    {
        $excepted = $this->createExercise();
        $res = $this->getExerciseService()->updateModuleEnable($excepted['id'], ['chapterEnable' => 1]);
        $this->assertEquals(1, $res['chapterEnable']);
    }

    public function testUpdateBaseInfo()
    {
        $excepted = $this->createExercise();
        $res = $this->getExerciseService()->updateBaseInfo(
            $excepted['id'],
            [
                'price' => 10.1,
                'isFree' => 0,
                'expiryDays' => 10,
                'expiryMode' => 'days',
            ]
        );

        $this->assertEquals(10.1, $res['price']);
        $this->assertEquals(0, $res['isFree']);
        $this->assertEquals(10, $res['expiryDays']);
        $this->assertEquals('days', $res['expiryMode']);
    }

    public function testSearchOrderByStudentNumAndLastDays()
    {
        $this->batchCreateExercise();
        $this->mockExerciseMembers();

        $exercises = $this->getExerciseService()->searchOrderByStudentNumAndLastDays([], 1, 0, 3);

        $this->assertEquals(3, $exercises[0]['id']);
        $this->assertEquals(2, $exercises[1]['id']);
        $this->assertEquals(1, $exercises[2]['id']);
    }

    public function testSearchOrderByRatingAndLastDays()
    {
        $this->batchCreateExercise();
        $this->mockReviews();

        $exercises = $this->getExerciseService()->searchOrderByRatingAndLastDays([], 1, 0, 3);

        $this->assertEquals(3, $exercises[0]['id']);
        $this->assertEquals(2, $exercises[1]['id']);
        $this->assertEquals(1, $exercises[2]['id']);
    }

    public function testCanTakeItemBankExercise()
    {
        $this->createExercise();
        $this->mockExerciseMembers();
        $this->mockUser();

        $result = $this->getExerciseService()->canTakeItemBankExercise(1);
        $this->assertEquals(true, $result);
    }

    public function testCanTakeItemBankExercise_whenExerciseNotFound_thenReturnFalse()
    {
        $result = $this->getExerciseService()->canTakeItemBankExercise(1);
        $this->assertEquals(false, $result);
    }

    public function getFindExercisesByLikeTitle()
    {
        $this->batchCreateExercise();
        $res = $this->getExerciseService()->findExercisesByLikeTitle('test');

        $this->assertEquals(3, count($res));
    }

    public function testTryTakeExercise()
    {
        $exercise = $this->createExercise();
        $member = $this->getExerciseMemberDao()->create([
            'exerciseId' => $exercise['id'],
            'userId' => 1,
            'role' => 'student',
            'remark' => 'aaa',
        ]);

        list($exerciseRes, $memberRes) = $this->getExerciseService()->tryTakeExercise($exercise['id']);

        $this->assertArrayEquals($exercise, $exerciseRes);
        $this->assertArrayEquals($memberRes, $memberRes);
    }

    protected function mockUser()
    {
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
    }

    protected function createExercise()
    {
        return $this->getExerciseService()->create(
            [
                'id' => 1,
                'title' => 'test',
                'questionBankId' => 1,
                'categoryId' => 1,
                'seq' => 1,
            ]
        );
    }

    protected function batchCreateExercise()
    {
        return $this->getExerciseDao()->batchCreate(
            [
                [
                    'id' => 1,
                    'title' => 'test1',
                    'questionBankId' => 1,
                    'categoryId' => 1,
                    'seq' => 1,
                ],
                [
                    'id' => 2,
                    'title' => 'test2',
                    'questionBankId' => 2,
                    'categoryId' => 1,
                    'seq' => 2,
                ],
                [
                    'id' => 3,
                    'title' => 'test3',
                    'questionBankId' => 3,
                    'categoryId' => 2,
                    'seq' => 3,
                ],
            ]
        );
    }

    protected function mockExerciseMembers()
    {
        $this->getExerciseMemberDao()->batchCreate([
            ['exerciseId' => 3, 'userId' => 1, 'role' => 'student', 'remark' => 'aaa'],
            ['exerciseId' => 3, 'userId' => 2, 'role' => 'student', 'remark' => 'bbb'],
            ['exerciseId' => 2, 'userId' => 2, 'role' => 'student', 'remark' => 'ccc'],
        ]);
    }

    protected function mockReviews()
    {
        $this->getReviewDao()->create([
            'userId' => 1,
            'targetType' => 'item_bank_exercise',
            'targetId' => 3,
            'rating' => 3,
            'content' => 'a',
        ]);

        $this->getReviewDao()->create([
            'userId' => 1,
            'targetType' => 'item_bank_exercise',
            'targetId' => 3,
            'rating' => 4,
            'content' => 'b',
        ]);

        $this->getReviewDao()->create([
            'userId' => 1,
            'targetType' => 'item_bank_exercise',
            'targetId' => 2,
            'rating' => 3,
            'content' => 'c',
        ]);
    }

    /**
     * @return ExerciseService
     */
    protected function getExerciseService()
    {
        return $this->createService('ItemBankExercise:ExerciseService');
    }

    /**
     * @return ExerciseDao
     */
    protected function getExerciseDao()
    {
        return $this->createDao('ItemBankExercise:ExerciseDao');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    protected function getExerciseMemberDao()
    {
        return $this->createDao('ItemBankExercise:ExerciseMemberDao');
    }

    protected function getReviewDao()
    {
        return $this->createDao('Review:ReviewDao');
    }
}
