<?php

namespace Tests\Unit\Course;

use Biz\BaseTestCase;
use Biz\Course\Service\LearningDataAnalysisService;

class LearningDataAnalysisServiceTest extends BaseTestCase
{
    public function testGetUserLearningProgress()
    {
        $this->mockBiz('Course:CourseService', array(
            array('functionName' => 'getCourse', 'returnValue' => array('id' => 1, 'compulsoryTaskNum' => 100)),
            array('functionName' => 'recountLearningData', 'returnValue' => array()),
        ));

        $this->mockBiz('Course:MemberService', array(
            array('functionName' => 'getCourseMember', 'returnValue' => array('learnedCompulsoryTaskNum' => 30)),
        ));

        $progress = $this->getLearningDataAnalysisService()->getUserLearningProgress(1, 1);

        $this->assertEquals(
            array('percent' => 30, 'decimal' => 0.3, 'finishedCount' => 30, 'total' => 100),
            $progress
        );
    }

    public function testGetUserLearningSchedule()
    {
        $fakeCourse = array(
            'id' => 1,
            'title' => 'course',
            'courseSetId' => 1,
            'expiryMode' => 'forever',
            'learnMode' => 'freeMode',
            'courseType' => 'normal',
            'compulsoryTaskNum' => 100,
        );
        $this->mockBiz('Course:CourseService', array(
            array('functionName' => 'getCourse', 'returnValue' => $fakeCourse),
            array('functionName' => 'recountLearningData', 'returnValue' => array()),
        ));

        $this->mockBiz('Course:MemberService', array(
            array('functionName' => 'getCourseMember', 'returnValue' => array('learnedCompulsoryTaskNum' => 30)),
        ));

        $this->mockBiz('Task:TaskService', array(
            array('functionName' => 'findToLearnTasksByCourseId', 'returnValue' => array()),
        ));

        $this->mockBiz('Task:TaskResultService', array(
            array('functionName' => 'countTaskResults', 'returnValue' => 10),
        ));

        $result = $this->getLearningDataAnalysisService()->getUserLearningSchedule(1, 123);
        unset($result['member']);

        $this->assertEquals(
            array(
                'taskCount' => 100,
                'progress' => 30.0,
                'taskResultCount' => 30,
                'toLearnTasks' => null,
                'taskPerDay' => 0,
                'planStudyTaskCount' => 0,
                'planProgressProgress' => 0,
            ),
            $result
        );
    }

    /**
     * @return LearningDataAnalysisService
     */
    private function getLearningDataAnalysisService()
    {
        return $this->createService('Course:LearningDataAnalysisService');
    }
}
