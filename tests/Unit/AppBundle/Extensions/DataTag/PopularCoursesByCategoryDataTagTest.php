<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\PopularCoursesByCategoryDataTag;

class PopularCoursesByCategoryDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $group = $this->getCategoryService()->addGroup(array('code' => 'course', 'name' => '课程分类', 'depth' => 2));
        $category1 = $this->getCategoryService()->createCategory(array(
            'name' => 'category 1',
            'code' => 'c1',
            'weight' => 1,
            'parentId' => 0,
            'groupId' => 1,
        ));
        $user1 = $this->getUserService()->register(array(
            'email' => '1234@qq.com',
            'nickname' => 'user1',
            'password' => '123456',
            'confirmPassword' => '123456',
            'createdIp' => '127.0.0.1',
        ));
        $user2 = $this->getUserService()->register(array(
            'email' => '12345@qq.com',
            'nickname' => 'user2',
            'password' => '123456',
            'confirmPassword' => '123456',
            'createdIp' => '127.0.0.1',
        ));
        $user3 = $this->getUserService()->register(array(
            'email' => '123456@qq.com',
            'nickname' => 'user3',
            'password' => '123456',
            'confirmPassword' => '123456',
            'createdIp' => '127.0.0.1',
        ));
        $course1 = array(
            'type' => 'normal',
            'title' => 'course1',
        );
        $course2 = array(
            'type' => 'normal',
            'title' => 'course2',
        );
        $course3 = array(
            'type' => 'normal',
            'title' => 'course3',
        );
        $course1 = $this->getCourseService()->createCourse($course1);
        $course2 = $this->getCourseService()->createCourse($course2);
        $course3 = $this->getCourseService()->createCourse($course3);
        $this->getCourseService()->publishCourse($course1['id']);
        $this->getCourseService()->publishCourse($course2['id']);
        $this->getCourseService()->publishCourse($course3['id']);
        $this->getCourseService()->updateCourse($course1['id'], array('categoryId' => $category1['id']));
        $this->getCourseService()->updateCourse($course2['id'], array('categoryId' => $category1['id']));
        $this->getCourseService()->updateCourse($course3['id'], array('categoryId' => $category1['id']));

        $this->getCourseMemberService()->becomeStudent($course1['id'], $user1['id']);
        $this->getCourseMemberService()->becomeStudent($course1['id'], $user2['id']);
        $this->getCourseMemberService()->becomeStudent($course2['id'], $user3['id']);
        $datatag = new PopularCoursesByCategoryDataTag();
        $courses = $datatag->getData(array('categoryId' => 1, 'count' => 5));
        $this->assertEquals(3, count($courses));
        $this->assertEquals($course1['id'], $courses[0]['id']);
        $this->assertEquals($course2['id'], $courses[1]['id']);
        $this->assertEquals($course3['id'], $courses[2]['id']);
    }

    public function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course:CourseService');
    }

    public function getUserService()
    {
        return $this->getServiceKernel()->createService('User:UserService');
    }

    public function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy:CategoryService');
    }

    protected function getCourseMemberService()
    {
        return $this->getServiceKernel()->createService('Course:MemberService');
    }
}
