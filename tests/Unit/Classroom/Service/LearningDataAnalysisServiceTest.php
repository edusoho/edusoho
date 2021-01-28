<?php

namespace Tests\Unit\Classroom\Service;

use Biz\BaseTestCase;
use Biz\Classroom\Service\LearningDataAnalysisService;

class LearningDataAnalysisServiceTest extends BaseTestCase
{
    public function testGetUserLearningProgress()
    {
        $classroomCourses = [
            ['courseId' => 1],
            ['courseId' => 2],
            ['courseId' => 3],
            ['courseId' => 4],
        ];
        $this->mockBiz('Classroom:ClassroomCourseDao', [
            ['functionName' => 'findByClassroomId', 'returnValue' => $classroomCourses],
        ]);

        $this->mockBiz('Course:LearningDataAnalysisService', [
            ['functionName' => 'getUserLearningCompulsoryProgressByCourseIds', 'returnValue' => ['percent' => 50, 'decimal' => 0.5, 'finishedCount' => 50, 'total' => 100]],
        ]);

        $progress = $this->getLearningDataAnalysisService()->getUserLearningProgress(1, 1);

        $this->assertEquals(
            ['percent' => 50, 'decimal' => 0.5, 'finishedCount' => 50, 'total' => 100],
            $progress
        );
    }

    /**
     * @return LearningDataAnalysisService
     */
    private function getLearningDataAnalysisService()
    {
        return $this->createService('Classroom:LearningDataAnalysisService');
    }
}
