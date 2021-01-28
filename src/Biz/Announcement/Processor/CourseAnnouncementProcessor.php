<?php

namespace Biz\Announcement\Processor;

use Biz\CloudPlatform\QueueJob\PushJob;
use Biz\Course\Service\CourseService;
use Biz\System\Service\SettingService;
use Codeages\Biz\Framework\Queue\Service\QueueService;
use Biz\User\Service\NotificationService;

class CourseAnnouncementProcessor extends AnnouncementProcessor
{
    public function checkManage($targetId)
    {
        try {
            $this->getCourseService()->tryManageCourse($targetId);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function checkTake($targetId)
    {
        return $this->getCourseService()->canTakeCourse($targetId);
    }

    public function getTargetShowUrl()
    {
        return 'course_show';
    }

    public function announcementNotification($targetId, $targetObject, $targetObjectShowUrl)
    {
        $conditions = array(
            'courseId' => $targetId,
            'role' => 'student',
        );
        $count = $this->getCourseMemberService()->countMembers($conditions);
        $members = $this->getCourseMemberService()->findCourseStudents($targetId, 0, $count);

        $result = false;
        if ($members) {
            $this->courseAnnouncementPush($targetId);
            $message = array('title' => $targetObject['title'],
                'url' => $targetObjectShowUrl,
                'type' => 'course', );
            foreach ($members as $member) {
                $result = $this->getNotificationService()->notify($member['userId'], 'learn-notice', $message);
            }
        }

        return $result;
    }

    private function courseAnnouncementPush($targetId)
    {
        if (!$this->isIMEnabled()) {
            return;
        }

        $course = $this->getCourseService()->getCourse($targetId);

        $conv = $this->getConversationService()->getConversationByTarget($course['id'], 'course-push');

        $from = array(
            'id' => $course['id'],
            'type' => 'course',
        );

        $to = array(
            'type' => 'course',
            'id' => 'all',
            'convNo' => $conv['no'],
        );

        $body = array(
            'type' => 'course.announcement.create',
            'courseId' => $course['id'],
            'title' => "《{$course['title']}》",
            'message' => "[课程公告] 你正在学习的课程《{$course['title']}》有一个新的公告，快去看看吧",
        );

        $this->createPushJob($from, $to, $body);
    }

    private function createPushJob($from, $to, $body)
    {
        $pushJob = new PushJob(array(
            'from' => $from,
            'to' => $to,
            'body' => $body,
        ));

        $this->getQueueService()->pushJob($pushJob);
    }

    public function isIMEnabled()
    {
        $setting = $this->getSettingService()->get('app_im', array());

        if (empty($setting) || empty($setting['enabled'])) {
            return false;
        }

        return true;
    }

    public function tryManageObject($targetId)
    {
        $course = $this->getCourseService()->tryManageCourse($targetId);

        return $course;
    }

    public function getTargetObject($targetId)
    {
        return $this->getCourseService()->getCourse($targetId);
    }

    public function getActions($action)
    {
        $config = array(
            'create' => 'AppBundle:Course/Announcement:create',
            'edit' => 'AppBundle:Course/Announcement:edit',
            'list' => 'AppBundle:Course/Announcement:list',
        );

        return $config[$action];
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }

    protected function getCourseMemberService()
    {
        return $this->biz->service('Course:MemberService');
    }

    /**
     * @return NotificationService
     */
    protected function getNotificationService()
    {
        return $this->biz->service('User:NotificationService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }

    /**
     * @return QueueService
     */
    protected function getQueueService()
    {
        return $this->biz->service('Queue:QueueService');
    }

    protected function getConversationService()
    {
        return $this->biz->service('IM:ConversationService');
    }
}
