<?php

namespace AppBundle\Controller\Classroom;

use AppBundle\Controller\BaseController;
use Biz\Announcement\Service\AnnouncementService;
use Biz\Classroom\Service\ClassroomService;

class AnnouncementController extends BaseController
{
    public function createAction($targetId)
    {
        $this->getClassroomService()->tryManageClassroom($targetId);
        $classroom = $this->getClassroomService()->getClassroom($targetId);

        return $this->render('classroom-manage/announcement/create-modal.html.twig', [
            'announcement' => ['id' => '', 'content' => ''],
            'targetObject' => $classroom,
            'targetType' => 'classroom',
            'targetId' => $targetId,
        ]);
    }

    public function editAction($targetId, $announcementId)
    {
        $this->getClassroomService()->tryManageClassroom($targetId);
        $classroom = $this->getClassroomService()->getClassroom($targetId);
        $announcement = $this->getAnnouncementService()->getAnnouncement($announcementId);

        return $this->render('classroom-manage/announcement/create-modal.html.twig', [
            'announcement' => $announcement,
            'targetObject' => $classroom,
            'targetType' => 'classroom',
            'targetId' => $targetId,
        ]);
    }

    public function listAction($targetId)
    {
        $this->getClassroomService()->tryManageClassroom($targetId);
        $classroom = $this->getClassroomService()->getClassroom($targetId);

        $conditions = [
            'targetType' => 'classroom',
            'targetId' => $classroom['id'],
        ];

        $announcements = $this->getAnnouncementService()->searchAnnouncements($conditions, ['createdTime' => 'DESC'], 0, 10);

        return $this->render('classroom-manage/announcement/list.html.twig', [
            'classroom' => $classroom,
            'announcements' => $announcements,
            'targetType' => 'classroom',
            'targetId' => $classroom['id'],
            'canManage' => true,
        ]);
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    /**
     * @return AnnouncementService
     */
    protected function getAnnouncementService()
    {
        return $this->createService('Announcement:AnnouncementService');
    }
}
