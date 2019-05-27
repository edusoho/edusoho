<?php

namespace Biz\WeChatNotification\Job;

use Codeages\Biz\Framework\Scheduler\AbstractJob;
use Biz\System\Service\LogService;
use Biz\WeChat\Service\WeChatService;
use Biz\Course\Service\CourseService;
use AppBundle\Common\ArrayToolkit;

class LiveNotificationJob extends AbstractJob
{
    public function execute()
    {
        $templateId = $this->getWeChatService()->getTemplateId('oneHourBeforeLiveOpen');
        if (empty($templateId)) {
            return;
        }

        try {
            $taskId = $this->args['taskId'];
            $url = $this->args['url'];
            $course = $this->getCourseService()->getCourse($task['courseId']);
            $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
            if ('published' != $courseSet['status'] || 'published' != $course['status']) {
                return;
            }

            $conditions = array('courseId' => $course['id'], 'role' => 'student',);
            $members = $this->getCourseMemberService()->searchMembers($conditions, array(), 0, PHP_INT_MAX, array('userId'));
            if (empty($members)) {
                return;
            }

            $userIds = ArrayToolkit::column($members, 'userId');
            $data = array(
                'userName' => array('value' => '同学'),
                'courseName' => array('value' => $courseSet['title']),
                'date' => array('value' => $task['startTime']),
                'remark' => array('value' => '不要迟到哦'),
            );
            $options = array('url' => $url);
        } catch (\Exception $e) {
        }
    }

    /**
     * @return LogService
     */
    private function getLogService()
    {
        return $this->biz->service('System:LogService');
    }

    /**
     * @return WeChatService
     */
    private function getWeChatService()
    {
        return $this->biz->service('WeChat:WeChatService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }

    protected function getCourseSetService()
    {
        return $this->biz->service('Course:CourseSetService');
    }

    protected function getCourseMemberService()
    {
        return $this->biz->service('Course:MemberService');
    }
}
