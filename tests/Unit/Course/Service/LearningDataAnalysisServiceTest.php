<?php

namespace Tests\Unit\Course\Service;

use Biz\BaseTestCase;
use Biz\Course\Service\LearningDataAnalysisService;

class LearningDataAnalysisServiceTest extends BaseTestCase
{
    public function testGetUserLearningProgress()
    {
        $this->mockBiz('Course:CourseService', [
            ['functionName' => 'getCourse', 'returnValue' => ['id' => 1, 'compulsoryTaskNum' => 100]],
            ['functionName' => 'recountLearningData', 'returnValue' => []],
        ]);

        $this->mockBiz('Course:MemberService', [
            ['functionName' => 'getCourseMember', 'returnValue' => ['learnedCompulsoryTaskNum' => 30]],
        ]);

        $progress = $this->getLearningDataAnalysisService()->getUserLearningProgress(1, 1);

        $this->assertEquals(
            ['percent' => 30, 'decimal' => 0.3, 'finishedCount' => 30, 'total' => 100],
            $progress
        );
    }

    public function testGetUserLearningSchedule()
    {
        $fakeCourse = [
            'id' => 1,
            'title' => 'course',
            'courseSetId' => 1,
            'expiryMode' => 'forever',
            'learnMode' => 'freeMode',
            'courseType' => 'normal',
            'compulsoryTaskNum' => 100,
        ];
        $this->mockBiz('Course:CourseService', [
            ['functionName' => 'getCourse', 'returnValue' => $fakeCourse],
            ['functionName' => 'recountLearningData', 'returnValue' => []],
        ]);

        $this->mockBiz('Course:MemberService', [
            ['functionName' => 'getCourseMember', 'returnValue' => ['learnedCompulsoryTaskNum' => 30]],
        ]);

        $this->mockBiz('Task:TaskService', [
            ['functionName' => 'findToLearnTasksByCourseId', 'returnValue' => []],
        ]);

        $this->mockBiz('Task:TaskResultService', [
            ['functionName' => 'countTaskResults', 'returnValue' => 10],
        ]);

        $result = $this->getLearningDataAnalysisService()->getUserLearningSchedule(1, 123);
        unset($result['member']);

        $this->assertEquals(
            [
                'taskCount' => 100,
                'progress' => 30.0,
                'taskResultCount' => 30,
                'toLearnTasks' => [],
                'taskPerDay' => 0,
                'planStudyTaskCount' => 0,
                'planProgressProgress' => 0,
            ],
            $result
        );
    }

    public function testGetUserLearningProgressByCourseIds()
    {
        $this->mockBiz('Course:LearningDataAnalysisDao', [
            [
                'functionName' => 'sumStatisticDataByCourseIdsAndUserId',
                'returnValue' => ['learnedNum' => 70, 'lessonNum' => 100],
                'withParams' => [[1], 1], ],
        ]);

        $this->mockBiz('Task:TaskService', [
            ['functionName' => 'countTasks', 'returnValue' => 100]
        ]);

        $result = $this->getLearningDataAnalysisService()->getUserLearningProgressByCourseIds([1], 1);

        $this->assertEquals([
            'finishedCount' => 70,
            'percent' => 70.0,
            'decimal' => 0.7,
            'total' => 100,
        ], $result);
    }

    /**
     * @return LearningDataAnalysisService
     */
    private function getLearningDataAnalysisService()
    {
        return $this->createService('Course:LearningDataAnalysisService');
    }
}
