<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\RecommendCoursesDataTag;

class RecommendCoursesDataTagTest extends BaseTestCase
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
            'type' => 'live',
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
        $this->getCourseService()->updateCourse($course2['id'], array('categoryId' => $category2['id']));
        $this->getCourseService()->updateCourse($course3['id'], array('categoryId' => $category2['id']));

        $this->getCourseService()->recommendCourse($course1['id'], 1);
        $this->getCourseService()->recommendCourse($course2['id'], 2);
        $this->getCourseService()->recommendCourse($course3['id'], 3);

        $datatag = new RecommendCoursesDataTag();
        $courses = $datatag->getData(array('count' => 5, 'type' => 'live'));
        $this->assertEquals(1, count($courses));
        $courses = $datatag->getData(array('count' => 5, 'type' => 'normal'));
        $this->assertEquals(2, count($courses));
        $courses = $datatag->getData(array('count' => 5, 'type' => 'live', 'categoryId' => $category2['id']));
        $this->assertEquals(1, count($courses));
        $courses = $datatag->getData(array('count' => 5, 'categoryCode' => $category1['code']));
        $this->assertEquals(3, count($courses));
    }

    public function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course:CourseService');
    }

    public function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy:CategoryService');
    }
}
