<?php

namespace Topxia\AdminBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AnnouncementController extends BaseController
{
    public function indexAction(Request $request)
    {
        $query = $request->query->all();
        $conditions = array(
            'targetType' => 'global'
        );

        $conditions = array_merge($conditions, $query);
        $conditions = $this->fillOrgCode($conditions);

        $paginator = new Paginator(
            $this->get('request'),
            $this->getAnnouncementService()->searchAnnouncementsCount($conditions),
            20
        );

        $announcements = $this->getAnnouncementService()->searchAnnouncements($conditions, array('createdTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount());

        $userIds = ArrayToolkit::column($announcements, 'userId');
        $users   = $this->getUserService()->findUsersByIds($userIds);

        $now = time();

        return $this->render('TopxiaAdminBundle:Announcement:index.html.twig', array(
            'paginator'     => $paginator,
            'announcements' => $announcements,
            'users'         => $users,
            'now'           => $now
        ));
    }

    public function createAction(Request $request)
    {
        $announcement = $request->request->all();

        if ($request->getMethod() == 'POST') {
            $announcement['targetType'] = 'global';
            $announcement['targetId']   = 0;
            $announcement['startTime']  = strtotime($announcement['startTime']);
            $announcement['endTime']    = strtotime($announcement['endTime']);

            $this->getAnnouncementService()->createAnnouncement($announcement);
        }

        return $this->render('TopxiaAdminBundle:Announcement:create.html.twig');
    }

    public function editAction(Request $request, $id)
    {
        $announcement = $this->getAnnouncementService()->getAnnouncement($id);

        if ($request->getMethod() == 'POST') {
            $announcement              = $request->request->all();
            $announcement['startTime'] = strtotime($announcement['startTime']);
            $announcement['endTime']   = strtotime($announcement['endTime']);

            $announcement = $this->getAnnouncementService()->updateAnnouncement($id, $announcement);
        }

        return $this->render('TopxiaAdminBundle:Announcement:create.html.twig', array(
            'announcement' => $announcement));
    }

    public function deleteAction($id)
    {
        $this->getAnnouncementService()->deleteAnnouncement($id);

        return new Response('true');
    }

    protected function getAnnouncementService()
    {
        return $this->getServiceKernel()->createService('Announcement.AnnouncementService');
    }
}
