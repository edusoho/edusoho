<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\LatestCoursesDataTag;

class LatestCoursesDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $group = $this->getCategoryService()->addGroup(array('code' => 'course', 'name' => '课程分类', 'depth' => 2));

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
            'title' => 'course2',
        );

        $category1 = $this->getCategoryService()->createCategory(array(
            'name' => 'category 1',
            'code' => 'c1',
            'weight' => 1,
            'parentId' => 0,
            'groupId' => $group['id'],
        ));

        $category2 = $this->getCategoryService()->createCategory(array(
            'name' => 'category 2',
            'code' => 'c2',
            'weight' => 1,
            'parentId' => $category1['id'],
            'groupId' => $group['id'],
        ));

        $course1 = $this->getCourseService()->createCourse($course1);
        $course2 = $this->getCourseService()->createCourse($course2);
        $course3 = $this->getCourseService()->createCourse($course3);

        $this->getCourseService()->publishCourse($course1['id']);
        $this->getCourseService()->publishCourse($course2['id']);
        $this->getCourseService()->publishCourse($course3['id']);

        $this->getCourseService()->updateCourse($course1['id'], array('categoryId' => $category1['id']));
        $this->getCourseService()->updateCourse($course2['id'], array('categoryId' => $category2['id']));
        $this->getCourseService()->updateCourse($course3['id'], array('categoryId' => $category1['id']));
        $this->getCourseService()->setCoursePrice($course1['id'], 'default', 3.11);

        $datatag = new LatestCoursesDataTag();
        $courses1 = $datatag->getData(array('count' => 5, 'notFree' => 0));
        $this->assertEquals(3, count($courses1));
        $courses2 = $datatag->getData(array('count' => 5, 'categoryId' => $category1['id'], 'notFree' => 1));
        $this->assertEquals(1, count($courses2));
        $courses3 = $datatag->getData(array('count' => 5, 'categoryId' => $category1['id']));
        $this->assertEquals(3, count($courses3));
        $courses4 = $datatag->getData(array('count' => 5));
        $this->assertEquals(3, count($courses4));
    }

    public function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    public function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }
}
