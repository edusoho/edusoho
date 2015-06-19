<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class AnnouncementController extends BaseController
{   
    public function indexAction()
    {   
        $conditions = array();

        $paginator=new Paginator(
            $this->get('request'),
            $this->getAnnouncementService()->searchAnnouncementsCount($conditions),
            20
            );

        $announcements = $this->getAnnouncementService()->searchAnnouncements($conditions, array('createdTime','DESC'), 
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount());

        $userIds =  ArrayToolkit::column($announcements, 'userId');
        $users=$this->getUserService()->findUsersByIds($userIds);
        
        $now = time();
        return $this->render('TopxiaAdminBundle:Announcement:index.html.twig',array(
            'paginator' => $paginator,
            'announcements' => $announcements,
            'users' => $users,
            'now' => $now,
            ));
    }

    public function createAction(Request $request)
    {   
        $announcement = $request->request->all();

        if ($request->getMethod() == "POST" ) {

            $this->getAnnouncementService()->createAnnouncement($announcement);
            
        }

        return $this->render('TopxiaAdminBundle:Announcement:create.html.twig');
    }

    public function editAction(Request $request, $id)
    {   
        $announcement = $this->getAnnouncementService()->getAnnouncement($id);

        if ($request->getMethod() == "POST" ) {

            $announcement = $request->request->all();

            $announcement = $this->getAnnouncementService()->updateAnnouncement($id, $announcement);
            
        }

        return $this->render('TopxiaAdminBundle:Announcement:create.html.twig',array(
            'announcement'=>$announcement));
    }

    public function deleteAction($id)
    {
        $this->getAnnouncementService()->deleteAnnouncement($id);

        return new Response("true");
    }

    protected function getAnnouncementService()
    {
        return $this->getServiceKernel()->createService('Announcement.AnnouncementService');
    }
}