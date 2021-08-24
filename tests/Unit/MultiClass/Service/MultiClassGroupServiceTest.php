<?php

namespace Tests\Unit\MultiClass\Service;

use Biz\Assistant\Service\AssistantStudentService;
use Biz\BaseTestCase;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\MultiClass\Dao\MultiClassProductDao;
use Biz\MultiClass\Service\MultiClassGroupService;
use Biz\MultiClass\Service\MultiClassProductService;
use Biz\MultiClass\Service\MultiClassService;
use Biz\User\Dao\UserDao;
use Biz\User\Service\UserService;

class MultiClassGroupServiceTest extends BaseTestCase
{
    public function testFindGroupsByIds()
    {
        $this->batchCreateGroup();

        $result = $this->getMultiClassGroupService()->findGroupsByIds([1, 2, 3]);

        $this->assertEquals(3, count($result));
    }

    public function testFindGroupsByMultiClassId()
    {
        $this->batchCreateGroup();

        $result = $this->getMultiClassGroupService()->findGroupsByMultiClassId(1);

        $this->assertEquals(4, count($result));
    }

    public function testGetMultiClassGroup()
    {
        $this->batchCreateGroup();

        $result = $this->getMultiClassGroupService()->getMultiClassGroup(1);

        $this->assertEquals('分组1', $result['name']);
    }

    public function testCreateMultiClassGroups()
    {
        $multiClass = $this->createMultiClass();

        $result = $this->getMultiClassGroupService()->createMultiClassGroups(3, $multiClass);

        $this->assertTrue($result);
    }

    public function testFindGroupsByCourseId()
    {
        $this->batchCreateGroup();

        $result = $this->getMultiClassGroupService()->findGroupsByCourseId(1);

        $this->assertEquals(4, count($result));
    }

    public function testGetLiveGroupByUserIdAndCourseId()
    {
        $this->createAssistantStudent();
        $this->createMultiClassLiveGroup();

        $result = $this->getMultiClassGroupService()->getLiveGroupByUserIdAndCourseId(1, 2, 1);

        $this->assertEquals(1, $result['id']);
    }

    public function testCreateLiveGroup()
    {
        $result = $this->createMultiClassLiveGroup();

        $this->assertEquals(1, $result['id']);
    }

    public function testBatchCreateLiveGroups()
    {
        $result = $this->batchCreateGroup();

        $this->assertTrue($result);
    }

    public function testSetGroupNewStudent()
    {
        $multiClass = $this->createMultiClass();

        $result = $this->getMultiClassGroupService()->setGroupNewStudent($multiClass, 1);

        $this->assertTrue($result);
    }

    public function testDeleteMultiClassGroup()
    {
        $this->batchCreateGroup();

        $result = $this->getMultiClassGroupService()->deleteMultiClassGroup(1);

        $this->assertEquals(1, $result);
    }

    public function testSortMultiClassGroup()
    {
        $this->batchCreateGroup();

        $this->getMultiClassGroupService()->sortMultiClassGroup(1);

        $group = $this->getMultiClassGroupService()->getMultiClassGroup(1);

        $this->assertEquals(1, $group['seq']);
    }

    public function testUpdateMultiClassGroup()
    {
        $this->batchCreateGroup();

        $this->getMultiClassGroupService()->updateMultiClassGroup(1, ['seq' => 2]);

        $group = $this->getMultiClassGroupService()->getMultiClassGroup(1);

        $this->assertEquals(2, $group['seq']);
    }

    public function testGetLatestGroup()
    {
        $this->batchCreateGroup();

        $result = $this->getMultiClassGroupService()->getLatestGroup(1);

        $this->assertEquals(4, $result['id']);
    }

    public function testBatchUpdateGroupAssistant()
    {
        $this->batchCreateGroup();
        $multiClass = $this->createMultiClass();
        $this->createAssistantStudent();

        $result = $this->getMultiClassGroupService()->batchUpdateGroupAssistant($multiClass['id'], [1], 1);

        $this->assertTrue($result);
    }

    public function createMultiClassLiveGroup()
    {
        $fields = [
            'id' => '1',
            'group_id' => 1,
            'live_code' => 1,
            'live_id' => 1,
            'created_time' => time(),
        ];

        return $this->getMulticlassLiveGroupDao()->create($fields);
    }

    public function createAssistantStudent()
    {
        $fields = [
            'id' => '1',
            'courseId' => 2,
            'studentId' => 1,
            'assistantId' => 1,
            'multiClassId' => 1,
            'group_id' => 1,
            'createdTime' => time(),
            'updatedTime' => time(),
        ];

        return $this->getAssistantStudentService()->create($fields);
    }

    protected function createMultiClass()
    {
        $this->createMultiClassProduct();
        $this->createCourse();
        $this->createCourseMember();
        $teacher = $this->createUser(['ROLE_TEACHER', 'ROLE_USER']);
        $assistant1 = $this->createUser(['ROLE_TEACHER', 'ROLE_USER', 'ROLE_TEACHER_ASSISTANT']);
        $assistant2 = $this->createUser(['ROLE_TEACHER', 'ROLE_USER', 'ROLE_TEACHER_ASSISTANT']);

        $fields = [
            'title' => 'multi class 1',
            'courseId' => 1,
            'productId' => 1,
            'copyId' => 0,
            'type' => 'group',
            'teacherId' => $teacher['id'],
            'assistantIds' => [$assistant1['id'], $assistant2['id']],
            'maxStudentNum' => 10,
            'isReplayShow' => 1,
            'group_limit_num' => 10,
        ];

        return $this->getMultiClassService()->createMultiClass($fields);
    }

    protected function createUser($role)
    {
        $userInfo = [
            'nickname' => 'test_nickname'.rand(0, 99999),
            'password' => 'test_password',
            'email' => rand(0, 99999).'@email.com',
        ];
        $user = $this->getUserService()->register($userInfo);
        $this->getUserDao()->update($user['id'], ['roles' => $role]);

        return $user;
    }

    public function createMultiClassProduct($fields = [])
    {
        $baseFields = [
            'title' => 'multi product 1',
            'type' => 'normal',
        ];
        $multiClassProduct = array_merge($baseFields, $fields);

        return $this->getMultiClassProductService()->createProduct($multiClassProduct);
    }

    protected function createCourseMember()
    {
        $defaultFields = [
            'id' => 3,
            'courseId' => 3,
            'classroomId' => 0,
            'multiClassId' => 1,
            'joinedType' => 'course',
            'userId' => 1,
            'role' => 'student',
            'learnedCompulsoryTaskNum' => 1,
            'courseSetId' => 1,
        ];

        return $this->getCourseMemberDao()->create($defaultFields);
    }

    protected function createCourse()
    {
        $courseSetFields = [
            'id' => 3,
            'title' => '课程1',
            'type' => 'normal',
        ];
        $courseSet = $this->getCourseSetService()->createCourseSet($courseSetFields);

        $courseFields = [
            'id' => 3,
            'title' => '课程1',
            'courseSetId' => $courseSet['id'],
            'expiryMode' => 'forever',
            'learnMode' => 'freeMode',
            'isDefault' => 1,
            'courseType' => 'normal',
        ];

        return $this->getCourseService()->createCourse($courseFields);
    }

    protected function batchCreateGroup()
    {
        return $this->getMulticlassGroupDao()->batchCreate([
            [
                'id' => 1,
                'name' => '分组1',
                'assistant_id' => 1,
                'multi_class_id' => 1,
                'course_id' => 1,
                'student_num' => 1,
                'seq' => 1,
            ],
            [
                'id' => 2,
                'name' => '分组2',
                'assistant_id' => 1,
                'multi_class_id' => 1,
                'course_id' => 1,
                'student_num' => 1,
                'seq' => 2,
            ],
            [
                'id' => 3,
                'name' => '分组3',
                'assistant_id' => 1,
                'multi_class_id' => 1,
                'course_id' => 1,
                'student_num' => 1,
                'seq' => 3,
            ],
            [
                'id' => 4,
                'name' => '分组4',
                'assistant_id' => 1,
                'multi_class_id' => 1,
                'course_id' => 1,
                'student_num' => 1,
                'seq' => 4,
            ],
        ]);
    }

    protected function getMultiClassLiveGroupDao()
    {
        return $this->createDao('MultiClass:MultiClassLiveGroupDao');
    }

    /**
     * @return AssistantStudentService
     */
    private function getAssistantStudentService()
    {
        return $this->createService('Assistant:AssistantStudentService');
    }

    /**
     * @return MultiClassProductService
     */
    protected function getMultiClassProductService()
    {
        return $this->createService('MultiClass:MultiClassProductService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return MultiClassGroupService
     */
    protected function getMultiClassGroupService()
    {
        return $this->createService('MultiClass:MultiClassGroupService');
    }

    /**
     * @return MultiClassProductDao
     */
    protected function getMulticlassGroupDao()
    {
        return $this->createDao('MultiClass:MultiClassGroupDao');
    }

    /**
     * @return MultiClassService
     */
    protected function getMultiClassService()
    {
        return $this->createService('MultiClass:MultiClassService');
    }

    protected function getCourseMemberDao()
    {
        return $this->createDao('Course:CourseMemberDao');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return UserDao
     */
    protected function getUserDao()
    {
        return $this->createService('User:UserDao');
    }
}
