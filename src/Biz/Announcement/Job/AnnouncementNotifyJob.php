<?php

namespace Biz\Announcement\Job;

use Biz\Classroom\Service\ClassroomService;
use Biz\CloudPlatform\QueueJob\PushJob;
use Biz\Course\Service\CourseService;
use Biz\System\Service\SettingService;
use Biz\User\Service\NotificationService;
use Codeages\Biz\Framework\Queue\Service\QueueService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class AnnouncementNotifyJob extends AbstractJob
{
    public function execute()
    {
        $targetId = $this->args['targetId'];
        $targetType = $this->args['targetType'];
        $params = $this->args['params'];
        $targetObject = $params['targetObject'];
        $targetObjectShowUrl = $params['targetObjectShowUrl'];
        $announcement = $params['announcement'];

        if ('course' == $targetType) {
            $count = $this->getCourseMemberService()->countMembers(['courseId' => $targetId, 'role' => 'student']);
            $members = $this->getCourseMemberService()->findCourseStudents($targetId, 0, $count);
        } else {
            $count = $this->getClassroomService()->searchMemberCount(['classroomId' => $targetId, 'role' => 'student']);
            $members = $this->getClassroomService()->searchMembers(
                ['classroomId' => $targetId, 'role' => 'student'],
                ['createdTime' => 'DESC'],
                0, $count
            );
        }

        if ($members) {
            ('course' == $targetType) ? $this->courseAnnouncementPush($targetId) : $this->classroomAnnouncementPush($targetId);
            $message = ['title' => $targetObject['courseSetTitle'].'-'.(empty($targetObject['title']) ? '默认计划' : $targetObject['title']),
                'url' => $targetObjectShowUrl,
                'type' => $targetType,
                'announcement_id' => $announcement['id'], ];
            foreach ($members as $member) {
                $this->getNotificationService()->notify($member['userId'], 'learn-notice', $message);
            }
        }
    }

    private function courseAnnouncementPush($targetId)
    {
        if (!$this->isIMEnabled()) {
            return;
        }

        $course = $this->getCourseService()->getCourse($targetId);

        $conv = $this->getConversationService()->getConversationByTarget($course['id'], 'course-push');

        $from = [
            'id' => $course['id'],
            'type' => 'course',
        ];

        $to = [
            'type' => 'course',
            'id' => 'all',
            'convNo' => $conv['no'],
        ];

        $body = [
            'type' => 'course.announcement.create',
            'courseId' => $course['id'],
            'title' => "《{$course['title']}》",
            'message' => "[课程公告] 你正在学习的课程《{$course['title']}》有一个新的公告，快去看看吧",
        ];

        $this->createPushJob($from, $to, $body);
    }

    private function classroomAnnouncementPush($targetId)
    {
        if (!$this->isIMEnabled()) {
            return;
        }

        $classroom = $this->getClassroomService()->getClassroom($targetId);

        $conv = $this->getConversationService()->getConversationByTarget($classroom['id'], 'classroom-push');

        $from = [
            'id' => $classroom['id'],
            'type' => 'classroom',
        ];

        $to = [
            'type' => 'classroom',
            'id' => 'all',
            'convNo' => $conv['no'],
        ];

        $body = [
            'type' => 'classroom.announcement.create',
            'classroomId' => $classroom['id'],
            'title' => "《{$classroom['title']}》",
            'message' => "[班级公告] 你正在学习的班级《{$classroom['title']}》有一个新的公告，快去看看吧",
        ];

        $this->createPushJob($from, $to, $body);
    }

    private function createPushJob($from, $to, $body)
    {
        $pushJob = new PushJob([
            'from' => $from,
            'to' => $to,
            'body' => $body,
        ]);

        $this->getQueueService()->pushJob($pushJob);
    }

    public function isIMEnabled()
    {
        $setting = $this->getSettingService()->get('app_im', []);

        if (empty($setting) || empty($setting['enabled'])) {
            return false;
        }

        return true;
    }

    /**
     * @return QueueService
     */
    protected function getQueueService()
    {
        return $this->biz->service('Queue:QueueService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }

    protected function getCourseMemberService()
    {
        return $this->biz->service('Course:MemberService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }

    /**
     * @return NotificationService
     */
    private function getNotificationService()
    {
        return $this->biz->service('User:NotificationService');
    }

    protected function getConversationService()
    {
        return $this->biz->service('IM:ConversationService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->biz->service('Classroom:ClassroomService');
    }
}
