<?php

namespace AppBundle\Controller\Classroom;

use AppBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;

class AnnouncementController extends BaseController
{
    public function createAction(Request $request, $targetId)
    {
        $this->getClassroomService()->tryManageClassroom($targetId);
        $classroom = $this->getClassroomService()->getClassroom($targetId);

        return $this->render('announcement/announcement-write-modal.html.twig', array(
            'announcement' => array('id' => '', 'content' => ''),
            'targetObject' => $classroom,
            'targetType'   => 'classroom',
            'targetId'     => $targetId
        ));
    }

    public function editAction(Request $request, $targetId, $announcementId)
    {
        $this->getClassroomService()->tryManageClassroom($targetId);
        $classroom    = $this->getClassroomService()->getClassroom($targetId);
        $announcement = $this->getAnnouncementService()->getAnnouncement($announcementId);

        return $this->render('announcement/announcement-write-modal.html.twig', array(
            'announcement' => $announcement,
            'targetObject' => $classroom,
            'targetType'   => 'classroom',
            'targetId'     => $targetId
        ));
    }

    public function listAction(Request $request, $targetId)
    {
        $this->getClassroomService()->tryManageClassroom($targetId);
        $classroom = $this->getClassroomService()->getClassroom($targetId);

        $conditions = array(
            'targetType' => 'classroom',
            'targetId'   => $classroom['id']
        );

        $announcements = $this->getAnnouncementService()->searchAnnouncements($conditions, array('createdTime' => 'DESC'), 0, 10);

        return $this->render('announcement/announcement-list-modal.html.twig', array(
            'announcements' => $announcements,
            'targetType'    => 'classroom',
            'targetId'      => $classroom['id'],
            'canManage'     => true
        ));
    }

    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    protected function getAnnouncementService()
    {
        return $this->createService('Announcement:AnnouncementService');
    }
}
