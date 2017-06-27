<?php

namespace Biz\Course\Job;

use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\System\Service\LogService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class RefreshCourseMemberLearningProgressJob extends AbstractJob
{
    public function execute()
    {
        try {
            $courseId = $this->args['courseId'];

            $memberUserIds = $this->getCourseMemberService()->findMemberUserIdsByCourseId($courseId);

            foreach ($memberUserIds as $memberUserId) {
                $this->getCourseService()->recountLearningData($courseId, $memberUserId);
            }
        } catch (\Exception $e) {
            $this->getLogService()->error('course', 'refresh_learning_progress', "重新刷新课程#{$courseId}下的学员的学习进度失败", array('error' => $e->getTraceAsString()));
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
     * @return LogService
     */
    private function getLogService()
    {
        return $this->biz->service('System:LogService');
    }
}
