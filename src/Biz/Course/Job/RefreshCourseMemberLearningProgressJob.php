<?php

namespace Biz\Course\Job;

use AppBundle\Common\ExceptionPrintingToolkit;
use Biz\Course\Dao\LearningDataAnalysisDao;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\System\Service\LogService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class RefreshCourseMemberLearningProgressJob extends AbstractJob
{
    private $step = 1000;

    public function execute()
    {
        try {
            $courseId = $this->args['courseId'];

            $memberUserIds = $this->getCourseMemberService()->findMemberUserIdsByCourseId($courseId);

            for ($i = 0; $i < count($memberUserIds) / $this->step; ++$i) {
                $userIds = array_slice($memberUserIds, $this->step * $i, $this->step);
                $this->getLearningDataAnalysisDao()->batchRefreshUserLearningData($courseId, $userIds);
            }
        } catch (\Exception $e) {
            $this->getLogService()->error('course', 'refresh_learning_progress', "重新刷新课程#{$courseId}下的学员的学习进度失败", ExceptionPrintingToolkit::printTraceAsArray($e));
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
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
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
}
