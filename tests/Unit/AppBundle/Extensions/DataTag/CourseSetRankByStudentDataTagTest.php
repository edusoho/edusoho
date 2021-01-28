<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\CourseSetRankByStudentDataTag;

class CourseSetRankByStudentDataTagTest extends BaseTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCountMissing()
    {
        $datatag = new CourseSetRankByStudentDataTag();
        $datatag->getData(array());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCountError()
    {
        $datatag = new CourseSetRankByStudentDataTag();
        $datatag->getData(array('count' => 101));
    }

    public function testGetData()
    {
        $this->mockBiz('Course:MemberService', array(
            array(
                'functionName' => 'setCourseTeachers',
                'returnValue' => true,
            ),
            array(
                'functionName' => 'setDefaultTeacher',
                'returnValue' => true,
            ),
            array(
                'functionName' => 'findCourseTeachers',
                'returnValue' => array(),
            ),
            array(
                'functionName' => 'countStudentMemberByCourseSetId',
                'returnValue' => 3,
            ),
        ));
        $this->mockBiz('Course:CourseMemberDao', array(
            array(
                'functionName' => 'count',
                'returnValue' => 3,
            ),
        ));

        $courseSet = $this->getCourseSetService()->createCourseSet(array('type' => 'normal', 'title' => 'course set1 title'));
        $this->getCourseSetService()->publishCourseSet($courseSet['id']);
        $course1 = $this->getCourseService()->createCourse(array('title' => 'course title', 'courseSetId' => $courseSet['id'], 'expiryMode' => 'forever', 'learnMode' => 'freeMode', 'courseType' => 'normal'));
        $this->getCourseService()->publishCourse($course1['id']);
        $course1 = $this->getCourseService()->updateCourseStatistics($course1['id'], array('studentNum'));
        $courseSet = $this->getCourseSetService()->updateCourseSetStatistics($courseSet['id'], array('studentNum'));

        $courseSet2 = $this->getCourseSetService()->createCourseSet(array('type' => 'normal', 'title' => 'course set2 title'));
        $this->getCourseSetService()->publishCourseSet($courseSet2['id']);
        $course2 = $this->getCourseService()->createCourse(array('title' => 'course title', 'courseSetId' => $courseSet2['id'], 'expiryMode' => 'forever', 'learnMode' => 'freeMode', 'courseType' => 'normal'));
        $this->getCourseService()->publishCourse($course2['id']);

        $datatag = new CourseSetRankByStudentDataTag();
        $data = $datatag->getData(array('count' => 1));
        $this->assertEquals(1, count($data));
        $this->assertEquals($courseSet['id'], $data[0]['id']);
        $this->assertEquals($course1['studentNum'], $data[0]['studentNum']);
        $this->assertEquals($course1['studentNum'], $courseSet['studentNum']);
    }

    private function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    private function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }
}
