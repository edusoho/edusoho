<?php

namespace Tests\Unit\Course;

use Biz\BaseTestCase;
use Biz\Course\Service\LearningDataAnalysisService;

class LearningDataAnalysisServiceTest extends BaseTestCase
{
    public function testGetUserLearningProgress()
    {
        $this->mockBiz('Course:CourseService', array(
            array('functionName' => 'getCourse', 'returnValue' => array('id' => 1, 'publishedTaskNum' => 100)),
        ));

        $this->mockBiz('Task:TaskService', array(
            array('functionName' => 'searchTasks', 'returnValue' => array(array('id' => 1), array('id' => 2))),
        ));

        $this->mockBiz('Task:TaskResultService', array(
            array('functionName' => 'countTaskResults', 'returnValue' => 30),
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
            'publishedTaskNum' => 100
        );
        $this->mockBiz('Course:CourseService', array(
            array('functionName' => 'getCourse', 'returnValue' => $fakeCourse),
        ));

        $this->mockBiz('Course:MemberService', array(
            array('functionName' => 'getCourseMember', 'returnValue' => 1),
        ));

        $this->mockBiz('Task:TaskService', array(
            array('functionName' => 'countTasks', 'returnValue' => 100),
            array('functionName' => 'searchTasks', 'returnValue' => array(array('id' => 1), array('id' => 2))),
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
                'progress' => 10,
                'taskResultCount' => 10,
                'toLearnTasks' => array(),
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