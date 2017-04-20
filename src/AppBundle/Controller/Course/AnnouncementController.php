<?php

namespace AppBundle\Controller\Course;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;

class AnnouncementController extends BaseController
{
    public function createAction(Request $request, $targetId)
    {
        $course = $this->getCourseService()->tryManageCourse($targetId);

        //TODO:这里需要根据用户所拥有的计划的权限展示
        $plans = $this->getCourseService()->findCoursesByCourseSetId($course['courseSetId']);

        $user = $this->getUser();
        $member = $user['id'] ? $this->getMemberService()->getCourseMember($course['id'], $user['id']) : null;

        return $this->render('course/announcement/write-modal.html.twig', array(
            'announcement' => array('id' => '', 'content' => ''),
            'targetObject' => $course,
            'targetType' => 'course',
            'targetId' => $targetId,
            'plans' => $plans,
            'member' => $member,
        ));
    }

    public function listAction(Request $request, $targetId)
    {
        $course = $this->getCourseService()->tryManageCourse($targetId);
        //TODO:这里需要根据用户所拥有的计划的权限展示
        $plans = $this->getCourseService()->findCoursesByCourseSetId($course['courseSetId']);
        $plans = ArrayToolkit::index($plans, 'id');

        $conditions = array(
            'targetType' => 'course',
            'targetIds' => ArrayToolkit::column($plans, 'id'),
        );

        $announcements = $this->getAnnouncementService()->searchAnnouncements($conditions, array('createdTime' => 'DESC'), 0, 10);

        return $this->render('course/announcement/list-modal.html.twig', array(
            'announcements' => $announcements,
            'targetType' => 'course',
            'targetId' => $course['id'],
            'canManage' => true,
            'plans' => $plans,
        ));
    }

    public function editAction(Request $request, $targetId, $announcementId)
    {
        $course = $this->getCourseService()->tryManageCourse($targetId);

        //TODO:这里需要根据用户所拥有的计划的权限展示
        $plans = $this->getCourseService()->findCoursesByCourseSetId($course['courseSetId']);

        $announcement = $this->getAnnouncementService()->getAnnouncement($announcementId);

        return $this->render('course/announcement/write-modal.html.twig', array(
            'announcement' => $announcement,
            'targetObject' => $course,
            'targetType' => 'course',
            'targetId' => $targetId,
            'plans' => $plans,
        ));
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getMemberService()
    {
        return $this->getBiz()->service('Course:MemberService');
    }

    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    protected function getAnnouncementService()
    {
        return $this->createService('Announcement:AnnouncementService');
    }
}
