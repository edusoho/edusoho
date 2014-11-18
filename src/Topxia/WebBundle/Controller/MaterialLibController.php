<?php

namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;
use Topxia\Service\Util\CloudClientFactory;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MaterialLibController extends BaseController {

	public function indexAction(Request $request, $type = "all", $viewMode = "thumb", $source = "upload") {
		$currentUser = $this->getCurrentUser ();
		$currentUserId = $currentUser['id'];
		
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
		$param ['currentUserId'] = $currentUserId;

		$paginator = new Paginator ( $request, $this->getUploadFileService ()->searchFileCount ( $param ) );
		
		$materialResults = $this->getUploadFileService ()->searchFiles ( $param, $sortBy, $paginator->getOffsetCount (), $paginator->getPerPageCount () );
		
		if ($viewMode == 'thumb') {
			$resultPage = 'TopxiaWebBundle:MaterialLib:material-thumb-view.html.twig';
		} else {
			$resultPage = 'TopxiaWebBundle:MaterialLib:material-list-view.html.twig';
		}
		
		$storageSetting = $this->getSettingService ()->get ( "storage" );
		
		return $this->render ( $resultPage, array (
				'currentUserId' => $currentUserId,
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
		$currentUser = $this->getCurrentUser ();
		$currentUserId = $currentUser['id'];
		
		$recentContacts = $this->getUploadFileService()->findRecentContacts($currentUserId);
		$allTeachers =  $this->getUserService()->searchUsers(array('roles'=> 'ROLE_TEACHER', 'locked'=>0), array('nickname', 'ASC'), 0, 100);
		
		return $this->render ( 'TopxiaWebBundle:MaterialLib:share-my-materials.html.twig', array (
				'recentContacts' => $recentContacts,
				'allTeachers' => $allTeachers,
				'currentUserId' => $currentUserId
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
		$currentUser = $this->getCurrentUser ();
		$currentUserId = $currentUser['id'];
		$targetUserIds = $request->get('targetUserIds');
		
		if (!empty($targetUserIds)) {
			$this->getUploadFileService()->shareFiles($currentUserId, $targetUserIds);
		}
		
		return $this->createJsonResponse(true);
	}
	
	public function cancelShareAction(Request $request){
		$targetUserId = $request->get('targetUserId' );
		$currentUser = $this->getCurrentUser ();
		$currentUserId = $currentUser['id'];
		
		if(!empty($targetUserId)){
			$this->getUploadFileService()->cancelShareFile($currentUserId, $targetUserId);
		}
		
		return $this->createJsonResponse ( true );
	}

	public function previewAction(Request $request, $fileId) {
		$user = $this->getCurrentUser ();

		$file = $this->getUploadFileService()->getFile($fileId);
		
		if (empty ( $file )) {
			throw $this->createNotFoundException ();
		}
		
		if ($file ['type'] == 'video') {
			if (! empty ( $file ['metas2'] ) && ! empty ( $file ['metas2'] ['sd'] ['key'] )) {
				$factory = new CloudClientFactory ();
				$client = $factory->createClient ();
				$hls = $client->generateHLSQualitiyListUrl ( $file ['metas2'], 3600 );
				
				if (isset ( $file ['convertParams'] ['convertor'] ) && ($file ['convertParams'] ['convertor'] == 'HLSEncryptedVideo')) {
					$token = $this->getTokenService()->makeToken('hlsvideo.view', array (
							'data' => $fileId,
							'times' => 1,
							'duration' => 3600
					) );
					
					$hlsKeyUrl = $this->generateUrl ( 'material_lib_file_preview_hlskeyurl', array (
							'fileId' => $fileId,
							'token' => $token ['token'] 
					), true );
					
					$headLeaderInfo = $this->getHeadLeaderInfo ();
					$hls = $client->generateHLSEncryptedListUrl ( $file ['convertParams'], $file ['metas2'], $hlsKeyUrl, $headLeaderInfo ['headLeaders'], $headLeaderInfo ['headLeaderHlsKeyUrl'], 3600 );
				} else {
					$hls = $client->generateHLSQualitiyListUrl ( $file ['metas2'], 3600 );
				}
			}
		}

		return $this->render ( 'TopxiaWebBundle:MaterialLib:preview-modal.html.twig', array (
				'user' => $user,
				'file' => $file,
				'hlsUrl' => (isset ( $hls ) and is_array($hls) and !empty($hls['url'])) ? $hls['url'] : '',
		));
	}
	
	public function hlskeyurlAction(Request $request, $fileId, $token)
	{
		$token = $this->getTokenService()->verifyToken('hlsvideo.view', $token);
		
		if (empty($token)) {
			$fakeKey = $this->getTokenService()->makeFakeTokenString(16);
			return new Response($fakeKey);
		}
	
		$file = $this->getUploadFileService()->getFile($fileId);
		
		if (empty($file)) {
			throw $this->createNotFoundException();
		}
	
		if (empty($file['convertParams']['hlsKey'])) {
			throw $this->createNotFoundException();
		}
	
		return new Response($file['convertParams']['hlsKey']);
	}
	
	public function pptAction(Request $request, $fileId)
	{
		$file = $this->getUploadFileService()->getFile($fileId);
		
		if (empty($file) || $file['type'] != 'ppt') {
			throw $this->createNotFoundException();
		}
	
		if ($file['convertStatus'] != 'success') {
			if ($file['convertStatus'] == 'error') {
				$message = sprintf('PPT文档转换失败，请重新转换。');
				return $this->createJsonResponse(array(
						'error' => array('code' => 'error', 'message' => $message),
				));
			} else {
				return $this->createJsonResponse(array(
						'error' => array('code' => 'processing', 'message' => 'PPT文档还在转换中，还不能查看，请稍等。'),
				));
			}
		}
	
		$factory = new CloudClientFactory();
		$client = $factory->createClient();
	
		$result = $client->pptImages($file['metas2']['imagePrefix'], $file['metas2']['length']. '');
	
		return $this->createJsonResponse($result);
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