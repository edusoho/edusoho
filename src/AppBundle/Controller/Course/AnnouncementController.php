<?php

namespace AppBundle\Controller\Course;

use AppBundle\Controller\BaseController;
use Biz\Course\Service\CourseSetService;
use Symfony\Component\HttpFoundation\Request;

class AnnouncementController extends BaseController
{
    public function createAction(Request $request, $targetId)
    {
        $course = $this->getCourseService()->tryManageCourse($targetId);

        return $this->render('course-manage/announcement/create-modal.html.twig', [
            'announcement' => ['id' => '', 'content' => ''],
            'targetObject' => $course,
            'targetType' => 'course',
            'targetId' => $targetId,
        ]);
    }

    public function listAction(Request $request, $targetId)
    {
        $course = $this->getCourseService()->tryManageCourse($targetId);
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);

        $conditions = [
            'targetType' => 'course',
            'targetId' => $course['id'],
        ];

        $announcements = $this->getAnnouncementService()->searchAnnouncements($conditions, ['createdTime' => 'DESC'], 0, 10);

        return $this->render('course-manage/announcement/list.html.twig', [
            'course' => $course,
            'courseSet' => $courseSet,
            'announcements' => $announcements,
            'targetType' => 'course',
            'targetId' => $course['id'],
            'canManage' => true,
        ]);
    }

    public function editAction(Request $request, $targetId, $announcementId)
    {
        $course = $this->getCourseService()->tryManageCourse($targetId);

        $announcement = $this->getAnnouncementService()->getAnnouncement($announcementId);

        return $this->render('course-manage/announcement/create-modal.html.twig', [
            'announcement' => $announcement,
            'targetObject' => $course,
            'targetType' => 'course',
            'targetId' => $targetId,
        ]);
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getMemberService()
    {
        return $this->getBiz()->service('Course:MemberService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    protected function getAnnouncementService()
    {
        return $this->createService('Announcement:AnnouncementService');
    }
}
