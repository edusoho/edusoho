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
        $classroomCourseRelations = $this->getClassroomCourseDao()->findByClassroomId($classroomId);
        $courseIds = array_column($classroomCourseRelations, 'courseId');

        return $this->getCourseLearningDataAnalysisService()->getUserLearningProgressByCourseIds($courseIds, $userId);
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
