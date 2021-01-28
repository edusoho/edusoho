<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\MemberRecentlyLearnedDataTag;

class MemberRecentlyLearnedDataTagTest extends BaseTestCase
{
    public function testTaskEmpty()
    {
        $this->mockBiz('Task:TaskService', array(
            array(
                'functionName' => 'getUserRecentlyStartTask',
                'returnValue' => array(),
            ),
        ));

        $datatag = new MemberRecentlyLearnedDataTag();
        $courseSet = $datatag->getData(array('user' => array('id' => 10)));

        $this->assertEmpty($courseSet);
    }

    public function testGetData()
    {
        $this->mockBiz('Task:TaskService', array(
            array(
                'functionName' => 'getUserRecentlyStartTask',
                'returnValue' => array('id' => 10, 'fromCourseSetId' => 1, 'courseId' => 2),
            ),
            array(
                'functionName' => 'getNextTask',
                'returnValue' => array('id' => 11, 'fromCourseSetId' => 1, 'courseId' => 2),
            ),
        ));

        $this->mockBiz('Course:CourseSetService', array(
            array(
                'functionName' => 'getCourseSet',
                'returnValue' => array('id' => 1),
            ),
        ));

        $this->mockBiz('Course:CourseService', array(
            array(
                'functionName' => 'getCourse',
                'returnValue' => array('id' => 2, 'teacherIds' => array(1)),
            ),
        ));

        $this->mockBiz('Course:MemberService', array(
            array(
                'functionName' => 'getCourseMember',
                'returnValue' => array('id' => 5, 'userId' => 10),
            ),
        ));

        $this->mockBiz('Course:LearningDataAnalysisService', array(
            array(
                'functionName' => 'getUserLearningProgress',
                'returnValue' => array('id' => 5),
            ),
        ));

        $this->mockBiz('User:UserService', array(
            array(
                'functionName' => 'findUsersByIds',
                'returnValue' => array(array('id' => 1)),
            ),
        ));

        $datatag = new MemberRecentlyLearnedDataTag();
        $courseSet = $datatag->getData(array('user' => array('id' => 10)));

        $this->assertNotEmpty($courseSet['course']);
        $this->assertNotEmpty($courseSet['course']['teachers']);
        $this->assertNotEmpty($courseSet['course']['nextLearnTask']);
        $this->assertNotEmpty($courseSet['course']['progress']);
    }

    protected function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }
}
