<?php

namespace Biz\Classroom\Service\Impl;

use Biz\BaseService;
use Biz\Classroom\Dao\ClassroomCourseDao;
use Biz\Classroom\Service\ClassroomService;
use Biz\Classroom\Service\LearningDataAnalysisService;

class LearningDataAnalysisServiceImpl extends BaseService implements LearningDataAnalysisService
{
    public function getUserLearningProgress($classroomId, $userId)
    {
        $progress = array(
            'percent' => 0,
            'decimal' => 0,
            'finishedCount' => 0,
            'total' => 0,
        );

        $classroomCourseRelations = $this->getClassroomCourseDao()->findByClassroomId($classroomId);
        foreach ($classroomCourseRelations as $classroomCourseRelation) {
            $courseProgress = $this->getCourseLearningDataAnalysisService()->getUserLearningProgress($classroomCourseRelation['courseId'], $userId);
            $progress['finishedCount'] += $courseProgress['finishedCount'];
            $progress['total'] += $courseProgress['total'];
        }

        $progress['percent'] = $progress['finishedCount'] ? round($progress['finishedCount'] / $progress['total'], 2) * 100 : 0;
        $progress['decimal'] = $progress['finishedCount'] ? round($progress['finishedCount'] / $progress['total'], 2) : 0;
        $progress['percent'] = $progress['percent'] > 100 ? 100 : $progress['percent'];
        $progress['decimal'] = $progress['decimal'] > 1 ? 1 : $progress['decimal'];

        return $progress;
    }

    /**
     * @return ClassroomCourseDao
     */
    private function getClassroomCourseDao()
    {
        return $this->createDao('Classroom:ClassroomCourseDao');
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    /**
     * @return \Biz\Course\Service\LearningDataAnalysisService
     */
    private function getCourseLearningDataAnalysisService()
    {
        return $this->createService('Course:LearningDataAnalysisService');
    }
}