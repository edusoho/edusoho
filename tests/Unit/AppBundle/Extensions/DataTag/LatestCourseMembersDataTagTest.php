<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use Topxia\Service\Common\ServiceKernel;
use AppBundle\Extensions\DataTag\LatestCourseMembersDataTag;

class LatestCourseMembersDataTagTest extends BaseTestCase
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

        $category2 = $this->getCategoryService()->createCategory(array(
            'name' => 'category 2',
            'code' => 'c2',
            'weight' => 1,
            'parentId' => $category1['id'],
            'groupId' => 1,
        ));

        $course1 = array(
            'type' => 'normal',
            'title' => 'course1',
        );
        $course2 = array(
            'type' => 'normal',
            'title' => 'course2',
        );

        $course1 = $this->getCourseService()->createCourse($course1);
        $course2 = $this->getCourseService()->createCourse($course2);

        $this->getCourseService()->publishCourse($course1['id']);
        $this->getCourseService()->publishCourse($course2['id']);

        $this->getCourseService()->updateCourse($course1['id'], array('categoryId' => $category1['id']));
        $this->getCourseService()->updateCourse($course2['id'], array('categoryId' => $category2['id']));

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
        $this->getCourseMemberService()->becomeStudent($course1['id'], $user1['id']);
        $this->getCourseMemberService()->becomeStudent($course2['id'], $user2['id']);

        $datatag = new LatestCourseMembersDataTag();
        $members1 = $datatag->getData(array('count' => 5, 'categoryId' => $category1['id']));
        $this->assertEquals(2, count($members1));
        $members2 = $datatag->getData(array('count' => 5, 'categoryId' => $category2['id']));
        $this->assertEquals(1, count($members2));
    }

    public function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course:CourseService');
    }

    public function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy:CategoryService');
    }

    public function getUserService()
    {
        return ServiceKernel::instance()->createService('User:UserService');
    }
}
