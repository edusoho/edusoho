<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use AppBundle\Extensions\DataTag\LatestClassroomsDataTag;
use Biz\BaseTestCase;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;

class LatestClassroomsDataTagTest extends BaseTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCountMissing()
    {
        $datatag = new LatestClassroomsDataTag();
        $datatag->getData([]);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCountError()
    {
        $datatag = new LatestClassroomsDataTag();
        $datatag->getData(['count' => 101]);
    }

    public function testGetData()
    {
        $course1 = $this->createCourse('Test Course 1');
        $courseIds = [$course1['id']];

        $classroom1 = [
            'title' => 'classroom1',
            'about' => 'classroom about 1',
        ];
        $classroom1 = $this->getClassroomService()->addClassroom($classroom1);
        $this->getClassroomService()->addCoursesToClassroom($classroom1['id'], $courseIds);
        $this->getClassroomService()->publishClassroom($classroom1['id']);

        $classroom2 = [
            'title' => 'classroom2',
            'about' => 'classroom about 2',
        ];
        $classroom2 = $this->getClassroomService()->addClassroom($classroom2);
        $this->getClassroomService()->addCoursesToClassroom($classroom2['id'], $courseIds);
        $this->getClassroomService()->publishClassroom($classroom2['id']);

        $classroom3 = [
            'title' => 'classroom2',
            'about' => 'classroom about 2',
        ];
        $classroom3 = $this->getClassroomService()->addClassroom($classroom3);
        $this->getClassroomService()->addCoursesToClassroom($classroom3['id'], $courseIds);
        $this->getClassroomService()->publishClassroom($classroom3['id']);
        $this->getClassroomService()->updateClassroom($classroom3['id'], ['teacherIds' => []]);

        $dataTag = new LatestClassroomsDataTag();
        $results = $dataTag->getData(['count' => '5']);
        $this->assertEquals(3, count($results));
        $this->assertArrayHasKey('users', $results[0]);
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
