<?php

namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class MaterialLibController extends BaseController {

	public function indexAction(Request $request, $type = "all", $viewMode = "thumb", $source = "upload") {
		$user = $this->getCurrentUser ();
		
		$keyWord = $request->query->get ( 'keyword' ) ?  : "";
		$sortBy = $request->query->get ( 'sortBy' ) ?  : "latestUpdated";
		
		$param = array ();
		
		if ($type != 'all') {
			$param ['type'] = $type;
		}
		
		if ($keyWord != '') {
			$param ['filename'] = $keyWord;
		}
		
		$param ['source'] = $source;
		$param ['currentUserId'] = $user['id'];
		
		$paginator = new Paginator ( $request, $this->getUploadFileService ()->searchFileCount ( $param ) );
		
		$materialResults = $this->getUploadFileService ()->searchFiles ( $param, $sortBy, $paginator->getOffsetCount (), $paginator->getPerPageCount () );
		
		if ($viewMode == 'thumb') {
			$resultPage = 'TopxiaWebBundle:MaterialLib:material-thumb-view.html.twig';
		} else {
			$resultPage = 'TopxiaWebBundle:MaterialLib:material-list-view.html.twig';
		}
		
		$storageSetting = $this->getSettingService ()->get ( "storage" );
		
		return $this->render ( $resultPage, array (
				'type' => $type,
				'materialResults' => $materialResults,
				'paginator' => $paginator,
				'storageSetting' => $storageSetting,
				'viewMode' => $viewMode,
				'source' => $source,
				'now' => time(),
		) );
	}

	public function deleteAction(Request $request, $id) {
		$this->getUploadFileService ()->deleteFile ( $id );
		
		return $this->createJsonResponse ( true);
	}
	
	//Get the users who is sharing material lib to current user
	public function findMySharingContactsAction(Request $request){
		$user = $this->getCurrentUser ();
		
		$mySharingContacts = $this->getUploadFileService()->findMySharingContacts($user['id']);

		return $this->createJsonResponse($mySharingContacts);
	}

	public function showShareAction(Request $request) {
		$user = $this->getCurrentUser ();
		
		$recentContacts = $this->getUploadFileService()->findRecentContacts($user['id']);
		
		return $this->render ( 'TopxiaWebBundle:MaterialLib:share-my-materials.html.twig', array (
				'recentContacts' => $recentContacts
		) );
	}
	
	public function showShareHistoryAction(Request $request){
		$user = $this->getCurrentUser ();
		
		$shareHistories = $this->getUploadFileService()->findShareHistory($user['id']);
		
		$targetUserIds = array();
		
		if(!empty($shareHistories)){
			foreach ($shareHistories as $history){
				array_push($targetUserIds, $history['targetUserId']);
			}
			
			$targetUsers = $this->getUserService()->findUsersByIds($targetUserIds);
		}
		
		return $this->render ( 'TopxiaWebBundle:MaterialLib:material-share-history.html.twig', array (
				'shareHistories' => $shareHistories,
				'targetUsers' => isset($targetUsers) ? $targetUsers : array(),
				'source' => 'myShareHistory'
		) );
	}
	
	public function saveShareAction(Request $request){
		$targetUserIds = $request->get('targetUserIds');
		
		if (!empty($targetUserIds)) {
			$this->getUploadFileService()->shareFiles($this->getCurrentUser()['id'], $targetUserIds);
		}
		
		return $this->createJsonResponse(true);
	}
	
	public function cancelShareAction(Request $request){
		$targetUserId = $request->get('targetUserId' );
		$currentUserId = $this->getCurrentUser()['id'];
		
		if(!empty($targetUserId)){
			$this->getUploadFileService()->cancelShareFile($currentUserId, $targetUserId);
		}
		
		return $this->createJsonResponse(true); 
// 		return $this->forward('TopxiaWebBundle:MaterialLib:showShareHistory');
	}

	protected function getSettingService() {
		return $this->getServiceKernel ()->createService ( 'System.SettingService' );
	}

	private function getUploadFileService() {
		return $this->getServiceKernel ()->createService ( 'File.UploadFileService' );
	}

	protected function getUserService() {
		return $this->getServiceKernel ()->createService ( 'User.UserService' );
	}
}