<?php

namespace Biz\Course\Job;

use Biz\AppLoggerConstant;
use Biz\Course\Dao\CourseDao;
use Biz\Course\Dao\CourseJobDao;
use Biz\Course\Dao\LearningDataAnalysisDao;
use Biz\Course\Service\MemberService;
use Biz\System\Service\LogService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class RefreshLearningProgressJob extends AbstractJob
{
    private $step = 1000;

    public function execute()
    {
        try {
            $courseIds = $this->getShouldRefreshCourseIds();

            $this->getLogService()->info(AppLoggerConstant::COURSE, 'refresh_learning_progress', '开始执行刷新学习进度的定时任务', $courseIds);

            foreach ($courseIds as $courseId) {
                $this->refreshLearningProgress($courseId);
            }

            $this->getLogService()->info(AppLoggerConstant::COURSE, 'refresh_learning_progress', '刷新学习进度的定时任务执行成功', $courseIds);
        } catch (\Exception $e) {
            $this->getLogService()->error(AppLoggerConstant::COURSE, 'refresh_learning_progress', '刷新学习进度的定时任务执行失败', array('error' => $e->getMessage()));
        }
    }

    private function refreshLearningProgress($courseId)
    {
        try {
            $memberUserIds = $this->getCourseMemberService()->findMemberUserIdsByCourseId($courseId);
            for ($i = 0; $i < count($memberUserIds) / $this->step; ++$i) {
                $userIds = array_slice($memberUserIds, $this->step * $i, $this->step);
                $this->getLearningDataAnalysisDao()->batchRefreshUserLearningData($courseId, $userIds);
            }

            $this->getCourseJobDao()->deleteByTypeAndCourseId('refresh_learning_progress', $courseId);

            $this->getLogService()->info(AppLoggerConstant::COURSE, 'refresh_learning_progress', "刷新计划#{$courseId}学习进度成功");
        } catch (\Exception $e) {
            $this->getLogService()->error(AppLoggerConstant::COURSE, 'refresh_learning_progress', "刷新计划#{$courseId}学习进度失败", array('error' => $e->getMessage()));
        }
    }

    private function getShouldRefreshCourseIds()
    {
        $courseJobs = $this->getCourseJobDao()->findByType('refresh_learning_progress');

        $courseJobs = array_filter($courseJobs, function ($courseJob) {
            return count(array_filter($courseJob['data'])) > 0;
        });

        $courseIds = array_column($courseJobs, 'courseId');

        $copyCourses = $this->getCourseDao()->findCoursesByParentIds($courseIds);

        $refreshCourseIds = array_merge(array_column($copyCourses, 'id'), $courseIds);

        return array_unique(array_values($refreshCourseIds));
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

    /**
     * @return LogService
     */
    private function getLogService()
    {
        return $this->biz->service('System:LogService');
    }

    /**
     * @return CourseJobDao
     */
    private function getCourseJobDao()
    {
        return $this->biz->dao('Course:CourseJobDao');
    }

    /**
     * @return CourseDao
     */
    private function getCourseDao()
    {
        return $this->biz->dao('Course:CourseDao');
    }
}
