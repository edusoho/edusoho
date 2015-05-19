<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Announcement\AnnouncementProcessor\AnnouncementProcessorFactory;


class AnnouncementController extends BaseController
{

	public function showAction(Request $request, $id, $targetId)
	{
        $announcement = $this->getAnnouncementService()->getAnnouncement($id);
        $processor = $this->getAnnouncementProcessor($announcement['targetType']);
        $targetObject = $processor->getTargetObject($targetId);

        $showPageName = $processor->getShowPageName($targetId);

		return $this->render('TopxiaWebBundle:Announcement:'.$showPageName,array(
			'announcement' => $announcement,
			'targetObject' => $targetObject,
		));
	}

	public function listAction($id, $targetType, $targetId){
		$conditions = array(
			'targetType' => $targetType,
			'targetId' => $targetId
		);

		$processor = $this->getAnnouncementProcessor($targetType);
		$canManage = $processor->checkManage($targetId);

		$announcements = $this->getAnnouncementService()->searchAnnouncements($conditions, array('createdTime','DESC'), 0, 10);

		return $this->render('TopxiaWebBundle:Announcement:announcement-list-modal.html.twig',array(
			'announcements' => $announcements,
			'currentId'=> $id,
			'targetType' => $targetType,
			'targetId' => $targetId,
			'canManage' => $canManage
		));
	}

	public function showAllAction(Request $request, $targetType, $targetId)
	{
		$conditions = array(
			'targetType' => $targetType,
			'targetId' => $targetId
		);

		$announcements = $this->getAnnouncementService()->searchAnnouncements($conditions, array('createdTime','DESC'), 0, 10000);
		$users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($announcements, 'userId'));

		return $this->render('TopxiaWebBundle:Announcement:announcement-show-all-modal.html.twig',array(
			'announcements'=>$announcements,
			'users'=>$users
		));
	}

	public function createAction(Request $request, $targetType, $targetId)
	{
		$processor = $this->getAnnouncementProcessor($targetType);
		$targetObject = $processor->tryManageObject($targetId);
		
	    if($request->getMethod() == 'POST'){
	    	$data = $request->request->all();
	    	$data['targetType'] = $targetType;
	    	$data['targetId'] = $targetId;
	    	$data['url'] = isset($data['url']) ? $data['url'] : '';
	    	$data['startTime'] = isset($data['startTime']) ? $data['startTime'] : time();
	    	$data['endTime'] = isset($data['endTime']) ? $data['endTime'] : time();

        	$announcement = $this->getAnnouncementService()->createAnnouncement($data);

        	if ($request->request->get('notify') == 'notify'){
        		$targetObjectShowRout = $processor->getTargetShowUrl();
        		$targetObjectShowUrl = $this->generateUrl($targetObjectShowRout, array('id'=>$targetId), true);

	        	$result = $processor->announcementNotification($targetId, $targetObject, $targetObjectShowUrl);
	        }

        	return $this->createJsonResponse(true);
		}

		return $this->render('TopxiaWebBundle:Announcement:announcement-write-modal.html.twig',array(
			'announcement' => array('id' => '', 'content' => ''),
			'targetObject' => $targetObject,
			'targetType' => $targetType,
		));
	}
	
	public function updateAction(Request $request, $id, $targetType, $targetId)
	{	
		$processor = $this->getAnnouncementProcessor($targetType);
		$targetObject = $processor->tryManageObject($targetId);

        $announcement = $this->getAnnouncementService()->getAnnouncement($id);

	    if($request->getMethod() == 'POST') {
	    	$data = $request->request->all();
	    	$data['targetType'] = $targetType;
	    	$data['targetId'] = $targetId;
	    	$data['startTime'] = isset($data['startTime']) ? $data['startTime'] : time();
	    	$data['endTime'] = isset($data['endTime']) ? $data['endTime'] : time();

        	$this->getAnnouncementService()->updateAnnouncement($id, $data);
	        return $this->createJsonResponse(true);
		}

		return $this->render('TopxiaWebBundle:Announcement:announcement-write-modal.html.twig',array(
			'targetObject' => $targetObject,
			'announcement' => $announcement,
			'targetType' => $targetType
		));
	}

	public function deleteAction(Request $request, $id, $targetType, $targetId)
	{
		$processor = $this->getAnnouncementProcessor($targetType);
		$targetObject = $processor->tryManageObject($targetId);
		
		$this->getAnnouncementService()->deleteAnnouncement($id);

		return $this->createJsonResponse(true);
	}

	public function blockAction(Request $request, $targetObject, $targetType)
	{
		$conditions = array(
			'targetType' => $targetType,
			'targetId' => $targetObject['id']
		);

		$processor = $this->getAnnouncementProcessor($targetType);
		$canManage = $processor->checkManage($targetObject['id']);
		$canTake = $processor->checkTake($targetObject['id']);

		$announcements = $this->getAnnouncementService()->searchAnnouncements($conditions, array('createdTime','DESC'), 0, 1);

		return $this->render('TopxiaWebBundle:Announcement:announcement-block.html.twig',array(
			'targetObject' => $targetObject,
			'announcements' => $announcements,
			'canManage' => $canManage,
			'canTake' => $canTake,
			'targetType' => $targetType
		));
	}


	private function getAnnouncementProcessor($targetType)
	{
		$processor = AnnouncementProcessorFactory::create($targetType);
		return $processor;
	}

	protected function getAnnouncementService()
    {
        return $this->getServiceKernel()->createService('Announcement.AnnouncementService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }   

}