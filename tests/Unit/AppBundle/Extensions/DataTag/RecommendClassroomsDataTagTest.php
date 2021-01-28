<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use AppBundle\Extensions\DataTag\RecommendClassroomsDataTag;
use Biz\BaseTestCase;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;

class RecommendClassroomsDataTagTest extends BaseTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCountMissing()
    {
        $datatag = new RecommendClassroomsDataTag();
        $datatag->getData([]);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCountError()
    {
        $datatag = new RecommendClassroomsDataTag();
        $datatag->getData(['count' => 101]);
    }

    public function testGetData()
    {
        $course1 = $this->createCourse('Test Course 1');
        $courseIds = [$course1['id']];

        $classroom1 = $this->getClassroomService()->addClassroom(['title' => 'classroom1', 'private' => 0]);
        $this->getClassroomService()->addCoursesToClassroom($classroom1['id'], $courseIds);
        $this->getClassroomService()->publishClassroom($classroom1['id']);
        $classroom2 = $this->getClassroomService()->addClassroom(['title' => 'classroom2', 'private' => 0]);
        $this->getClassroomService()->addCoursesToClassroom($classroom2['id'], $courseIds);
        $this->getClassroomService()->publishClassroom($classroom2['id']);
        $classroom3 = $this->getClassroomService()->addClassroom(['title' => 'classroom3', 'private' => 0]);
        $this->getClassroomService()->addCoursesToClassroom($classroom3['id'], $courseIds);
        $this->getClassroomService()->publishClassroom($classroom3['id']);

        $this->getClassroomService()->recommendClassroom($classroom1['id'], 11);
        $this->getClassroomService()->recommendClassroom($classroom2['id'], 12);

        $datatag = new RecommendClassroomsDataTag();
        $classrooms = $datatag->getData(['count' => 3]);
        $this->assertEquals(3, count($classrooms));
    }

    private function getCategoryService()
    {
        return $this->createService('Taxonomy:CategoryService');
    }

    private function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    /**
     * @return CourseSetService
     */
    private function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    private function mockCourse($title = 'Test Course 1')
    {
        return [
            'title' => $title,
            'courseSetId' => 1,
            'learnMode' => 'freeMode',
            'expiryMode' => 'forever',
            'courseType' => 'normal',
        ];
    }

    private function createCourse($title)
    {
        $courseSet = [
            'title' => '新课程开始！',
            'type' => 'normal',
        ];

        $courseSet = $this->getCourseSetService()->createCourseSet($courseSet);
        $course = $this->mockCourse($title);
        $course['courseSetId'] = $courseSet['id'];

        return $this->getCourseService()->createCourse($course);
    }
}
