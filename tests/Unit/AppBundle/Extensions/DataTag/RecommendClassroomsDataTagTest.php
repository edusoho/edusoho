<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\RecommendClassroomsDataTag;

class RecommendClassroomsDataTagTest extends BaseTestCase
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

        $classroom1 = $this->getClassroomService->addClassroom(array('title' => 'classroom1', 'private' => 0));
        $classroom2 = $this->getClassroomService->addClassroom(array('title' => 'classroom2', 'private' => 0));
        $this->getClassroomService->addCourse($classroom1['id'], $course1['id']);
        $this->getClassroomService->addCourse($classroom2['id'], $course2['id']);
        $this->getClassroomService->recommendClassroom($classroom1['id'], 11);
        $this->getClassroomService->recommendClassroom($classroom2['id'], 12);

        $datatag = new RecommendClassroomsDataTag();
        $classrooms = $datatag->getData(array('count' => 5));
        $this->assertEquals(2, count($classrooms));
    }

    public function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course:CourseService');
    }

    public function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy:CategoryService');
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:ClassroomService');
    }
}
