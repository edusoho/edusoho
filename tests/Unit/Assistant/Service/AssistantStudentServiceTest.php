<?php

namespace Tests\Unit\Assistant\Service;

use Biz\Assistant\Service\AssistantStudentService;
use Biz\BaseTestCase;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\MemberService;
use Biz\MultiClass\Dao\MultiClassDao;
use Biz\MultiClass\Dao\MultiClassProductDao;
use Biz\MultiClass\Service\MultiClassProductService;
use Biz\MultiClass\Service\MultiClassService;
use Biz\User\Dao\UserDao;
use Biz\User\Service\UserService;

class AssistantStudentServiceTest extends BaseTestCase
{
    public function testSetAssistantStudents()
    {
        $multiClass = $this->createMultiClass();
        $this->batchCreateCourseMembers();

        $result = $this->getAssistantStudentService()->setAssistantStudents($multiClass['courseId'], $multiClass['id']);
        $this->assertEquals(true, $result);
    }

    protected function createMultiClass()
    {
        $product = $this->createMultiClassProduct();
        $course = $this->createCourse();
        $teacher = $this->createUser(['ROLE_TEACHER', 'ROLE_USER']);
        $assistant1 = $this->createUser(['ROLE_TEACHER', 'ROLE_USER', 'ROLE_TEACHER_ASSISTANT']);
        $assistant2 = $this->createUser(['ROLE_TEACHER', 'ROLE_USER', 'ROLE_TEACHER_ASSISTANT']);

        $fields = [
            'title' => 'multi class 1',
            'courseId' => $course['id'],
            'productId' => $product['id'],
            'copyId' => 0,
            'teacherId' => $teacher['id'],
            'assistantIds' => [$assistant1['id'], $assistant2['id']],
        ];

        return $this->getMultiClassService()->createMultiClass($fields);
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

    protected function batchCreateCourseMembers()
    {
        $member1 = [
            'id' => 99,
            'courseId' => 99,
            'userId' => 1,
            'courseSetId' => 99,
            'joinedType' => 'course',
            'role' => 'student',
            'deadline' => time(),
        ];

        return $this->getCourseMemberService()->batchCreateMembers([$member1]);
    }

    protected function createCourse()
    {
        $courseSetFields = [
            'title' => '课程1',
            'type' => 'normal',
        ];
        $courseSet = $this->getCourseSetService()->createCourseSet($courseSetFields);

        $courseFields = [
            'title' => '课程1',
            'courseSetId' => $courseSet['id'],
            'expiryMode' => 'forever',
            'learnMode' => 'freeMode',
            'isDefault' => 1,
            'courseType' => 'normal',
        ];

        return $this->getCourseService()->createCourse($courseFields);
    }

    /**
     * @return MultiClassDao
     */
    protected function getMultiClassDao()
    {
        return $this->createDao('MultiClass:MultiClassDao');
    }

    /**
     * @return MultiClassService
     */
    protected function getMultiClassService()
    {
        return $this->createService('MultiClass:MultiClassService');
    }

    /**
     * @return MultiClassProductService
     */
    protected function getMultiClassProductService()
    {
        return $this->createService('MultiClass:MultiClassProductService');
    }

    /**
     * @return MultiClassProductDao
     */
    protected function getMultiClassProductDao()
    {
        return $this->createDao('MultiClass:MultiClassProductDao');
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
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return AssistantStudentService
     */
    protected function getAssistantStudentService()
    {
        return $this->createService('Assistant:AssistantStudentService');
    }

    /**
     * @return UserDao
     */
    protected function getUserDao()
    {
        return $this->createService('User:UserDao');
    }
}
