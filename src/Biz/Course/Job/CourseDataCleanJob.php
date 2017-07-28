<?php

namespace Biz\Course\Job;

use Biz\Course\Dao\LearningDataAnalysisDao;
use Biz\Course\Service\MemberService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class CourseDataCleanJob extends AbstractJob
{
    public function execute()
    {
        $this->cleanStudentLearningData();
    }

    private function cleanStudentLearningData()
    {
        $courseIds = $this->biz['db']->fetchAll('SELECT id FROM course_v8', array());

        foreach ($courseIds as $courseId) {
            $this->refreshLearningProgress($courseId['id']);
        }

        unset($courses);
    }

    private function refreshLearningProgress($courseId)
    {
        $memberUserIds = $this->getCourseMemberService()->findMemberUserIdsByCourseId($courseId);
        for ($i = 0; $i < count($memberUserIds) / $this->step; ++$i) {
            $userIds = array_slice($memberUserIds, $this->step * $i, $this->step);
            $this->getLearningDataAnalysisDao()->batchRefreshUserLearningData($courseId, $userIds);
        }
    }

    /**
     * @return MemberService
     */
    private function getCourseMemberService()
    {
        return $this->biz->service('Course:MemberService');
    }

    /**
     * @return LearningDataAnalysisDao
     */
    private function getLearningDataAnalysisDao()
    {
        return $this->biz->dao('Course:LearningDataAnalysisDao');
    }
}