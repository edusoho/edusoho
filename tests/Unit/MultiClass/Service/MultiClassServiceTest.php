<?php

namespace Tests\Unit\MultiClass\Service;

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

class MultiClassServiceTest extends BaseTestCase
{
    public function testFindByProductIds()
    {
        $multiClass1 = $this->createMultiClass();
        $multiClass2 = $this->createMultiClass();
        $findMultiClass = $this->getMultiClassService()->findByProductIds([$multiClass1['productId'], $multiClass2['productId']]);
        $this->assertCount(2, $findMultiClass);
    }

    public function testFindByProductId()
    {
        $multiClass = $this->createMultiClass();
        $findMultiClass = $this->getMultiClassService()->findByProductIds($multiClass);
        $this->assertCount(1, $findMultiClass);
    }

    public function testGetMultiClass()
    {
        $createMultiClass = $this->createMultiClass();

        $getMultiClass = $this->getMultiClassService()->getMultiClass($createMultiClass['id']);

        $this->assertArrayValueEquals($createMultiClass, $getMultiClass);
    }

    public function testCreateMultiClass()
    {
        $multiClass = $this->createMultiClass();
        $getMultiClass = $this->getMultiClassService()->getMultiClass($multiClass['id']);
        $teacher = $this->getCourseMemberService()->findMultiClassMemberByMultiClassIdAndRole($multiClass['id'], 'teacher');
        $assistants = $this->getCourseMemberService()->findMultiClassMemberByMultiClassIdAndRole($multiClass['id'], 'assistant');

        $this->assertArrayValueEquals($multiClass, $getMultiClass);
        $this->assertCount(1, $teacher);
        $this->assertCount(2, $assistants);
    }

    public function testUpdateMultiClass()
    {
        $multiClass = $this->createMultiClass();
        $teacher = $this->createUser(['ROLE_TEACHER', 'ROLE_USER']);
        $assistant1 = $this->createUser(['ROLE_TEACHER', 'ROLE_USER', 'ROLE_TEACHER_ASSISTANT']);
        $assistant2 = $this->createUser(['ROLE_TEACHER', 'ROLE_USER', 'ROLE_TEACHER_ASSISTANT']);
        $assistant3 = $this->createUser(['ROLE_TEACHER', 'ROLE_USER', 'ROLE_TEACHER_ASSISTANT']);

        $updateFields = [
            'title' => 'multi class 2',
            'courseId' => $multiClass['courseId'],
            'productId' => $multiClass['productId'],
            'copyId' => 0,
            'teacherId' => $teacher['id'],
            'assistantIds' => [$assistant1['id'], $assistant2['id'], $assistant3['id']],
        ];
        $newMultiClass = $this->getMultiClassService()->updateMultiClass($multiClass['id'], $updateFields);
        $teacher = $this->getCourseMemberService()->findMultiClassMemberByMultiClassIdAndRole($newMultiClass['id'], 'teacher');
        $assistants = $this->getCourseMemberService()->findMultiClassMemberByMultiClassIdAndRole($newMultiClass['id'], 'assistant');

        $this->assertEquals('multi class 2', $newMultiClass['title']);
        $this->assertCount(1, $teacher);
        $this->assertCount(3, $assistants);
    }

    public function testDeleteMultiClass()
    {
        $multiClass = $this->createMultiClass();
        $this->getMultiClassService()->deleteMultiClass($multiClass['id']);
        $getMultiClass = $this->getMultiClassService()->getMultiClass($multiClass['id']);
        $teacher = $this->getCourseMemberService()->findMultiClassMemberByMultiClassIdAndRole($multiClass['id'], 'teacher');
        $assistants = $this->getCourseMemberService()->findMultiClassMemberByMultiClassIdAndRole($multiClass['id'], 'assistant');

        $this->assertEmpty($getMultiClass);
        $this->assertCount(0, $teacher);
        $this->assertCount(0, $assistants);
    }

    public function testSearchMultiClassJoinCourse()
    {
        $this->createMultiClass();
        $conditions = [
            'productId' => 1,
        ];
        $searchMultiClass = $this->getMultiClassService()->searchMultiClassJoinCourse($conditions, [], 0, PHP_INT_MAX);

        $this->assertNotEmpty($searchMultiClass);
    }

    public function testCountMultiClass()
    {
        $this->createMultiClass();
        $conditions = [
            'productId' => 1,
        ];
        $countMultiClass = $this->getMultiClassService()->countMultiClass($conditions);

        $this->assertEquals(1, $countMultiClass);
    }

    public function testGetMultiClassByTitle()
    {
        $createMultiClass = $this->createMultiClass();
        $getMultiClass = $this->getMultiClassService()->getMultiClassByTitle('multi class 1');

        $this->assertArrayValueEquals($createMultiClass, $getMultiClass);
    }

    public function testCloneMultiClass()
    {
        $this->createMultiClassDefaultProduct();
        $multiClass = $this->createMultiClass();
        $this->getMultiClassProductDao()->create(['title' => '系统默认', 'type' => 'default']);
        $cloneMultiClass = [
            'title' => '复制课程',
            'productId' => 1,
        ];
        $newMultiClass = $this->getMultiClassService()->cloneMultiClass($multiClass['id'], $cloneMultiClass);
        $this->assertEquals('复制课程', $newMultiClass['title']);
    }

    public function testGetMultiClassByCourseId()
    {
        $createMultiClass = $this->createMultiClass();
        $getMultiClass = $this->getMultiClassService()->getMultiClassByCourseId($createMultiClass['courseId']);
        $this->assertNotEmpty($getMultiClass);
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
            'maxStudentNum' => 10,
            'isReplayShow' => 1,
        ];

        return $this->getMultiClassService()->createMultiClass($fields);
    }

    public function createMultiClassDefaultProduct()
    {
        $fields = [
            'title' => '默认产品',
            'type' => 'default',
        ];

        return $this->getMultiClassProductDao()->create($fields);
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
     * @return UserDao
     */
    protected function getUserDao()
    {
        return $this->createService('User:UserDao');
    }
}
