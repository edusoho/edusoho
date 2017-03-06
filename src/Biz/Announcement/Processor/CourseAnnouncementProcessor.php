<?php

namespace Biz\Announcement\Processor;

use Topxia\Service\Common\ServiceKernel;
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
            $message = array('title' => $targetObject['title'],
                'url' => $targetObjectShowUrl,
                'type' => 'course', );
            foreach ($members as $member) {
                $result = $this->getNotificationService()->notify($member['userId'], 'learn-notice', $message);
            }
        }

        return $result;
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

    protected function getCourseService()
    {
        return ServiceKernel::instance()->getBiz()->service('Course:CourseService');
    }

    protected function getCourseMemberService()
    {
        return ServiceKernel::instance()->getBiz()->service('Course:MemberService');
    }

    /**
     * @return NotificationService
     */
    protected function getNotificationService()
    {
        return ServiceKernel::instance()->createService('User:NotificationService');
    }
}
