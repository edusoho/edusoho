<?php

namespace Tests\Unit\Classroom\Service;

use Biz\BaseTestCase;
use Biz\Classroom\Service\LearningDataAnalysisService;

class LearningDataAnalysisServiceTest extends BaseTestCase
{
    public function testGetUserLearningProgress()
    {
        $classroomCourses = array(
            array('courseId' => 1),
            array('courseId' => 2),
            array('courseId' => 3),
            array('courseId' => 4),
        );
        $this->mockBiz('Classroom:ClassroomCourseDao', array(
            array('functionName' => 'findByClassroomId', 'returnValue' => $classroomCourses),
        ));

        $this->mockBiz('Course:LearningDataAnalysisService', array(
            array('functionName' => 'getUserLearningProgressByCourseIds', 'returnValue' => array('percent' => 50, 'decimal' => 0.5, 'finishedCount' => 50, 'total' => 100)),
        ));

        $progress = $this->getLearningDataAnalysisService()->getUserLearningProgress(1, 1);

        $this->assertEquals(
            array('percent' => 50, 'decimal' => 0.5, 'finishedCount' => 50, 'total' => 100),
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
