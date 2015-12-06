<?php

namespace MaterialLib\MaterialLibBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;
use Topxia\Service\Util\CloudClientFactory;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Topxia\WebBundle\Controller\BaseController;

class MaterialLibController extends BaseController {
	/**
	 * Browse material lib. Use can switch between file types and search by keywords.
	 * @param Request $request HTTP request
	 * @param string $type file type (values: video|audio|document|image|ppt|other)
	 * @param string $viewMode viewing mode (values: thumb|list)
	 * @param string $source source of the file (values: upload|shared)
	 * @return \Symfony\Component\HttpFoundation\Response the HTTP response
	 */
	public function indexAction(Request $request, $type = "all", $viewMode = "thumb", $source = "upload") {
		$currentUser = $this->getCurrentUser ();
		if (!$currentUser->isTeacher() && !$currentUser->isAdmin()) {
			throw $this->createAccessDeniedException('您无权访问此页面');
		}

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

		$paginator = new Paginator ( $request, $this->getUploadFileService ()->searchFileCount ( $param ), 19 );
		
		$materialResults = $this->getUploadFileService ()->searchFiles ( $param, $sortBy, $paginator->getOffsetCount (), $paginator->getPerPageCount () );
		
		//Find the owners of all the files. This will be displayed for the files shared by other users.
		$createdUserIds = ArrayToolkit::column($materialResults, "createdUserId");

		$createdUsers = $this->getUserService()->findUsersByIds($createdUserIds);

		//Return different views according to current viewing mode
		if ($viewMode == 'thumb') {
			$resultPage = 'MaterialLibBundle:MaterialLib:material-thumb-view.html.twig';
		} else {
			$resultPage = 'MaterialLibBundle:MaterialLib:material-list-view.html.twig';
		}
		
		$storageSetting = $this->getSettingService ()->get ( "storage" );
		
		return $this->render ( $resultPage, array (
				'currentUserId' => $currentUserId,
				'type' => $type,
				'materialResults' => $materialResults,
				'createdUsers' => $createdUsers,
				'paginator' => $paginator,
				'storageSetting' => $storageSetting,
				'viewMode' => $viewMode,
				'source' => $source,
				'now' => time(),
		) );
	}

	/**
	 * Delete a file. Only the owner or the super admin can delete a file.
	 * @param Request $request HTTP request
	 * @param unknown $id id of the file to be deleted
	 * @return \Symfony\Component\HttpFoundation\JsonResponse JSON response
	 */
	public function deleteAction(Request $request, $id) {
		$user = $this->getCurrentUser ();
		if (!$user->isTeacher() && !$user->isAdmin()) {
			throw $this->createAccessDeniedException('您无权访问此页面');
		}

		$file = $this->getUploadFileService()->getFile($id);
		
		if (empty ( $file )) {
			throw $this->createNotFoundException ();
		}elseif($user["id"] != $file["createdUserId"] && !in_array('ROLE_SUPER_ADMIN', $user['roles'])){
			//Current user is not either the owner or super admin
			throw $this->createAccessDeniedException("您没有权限删除此文件！");
		}else{
			$this->getUploadFileService ()->deleteFile ( $id );
			
			return $this->createJsonResponse ( true);
		}
	}
	
	/**
	 * Get the users who is sharing material lib to current user. Inactive sharing contacts will not be included in the results.
	 * @param Request $request HTTP request
	 * @return \Symfony\Component\HttpFoundation\JsonResponse JSON response
	 */
	public function findMySharingContactsAction(Request $request){
		$user = $this->getCurrentUser ();
		if (!$user->isTeacher() && !$user->isAdmin()) {
			throw $this->createAccessDeniedException('您无权访问此页面');
		}

		$mySharingContacts = $this->getUploadFileService()->findMySharingContacts($user['id']);

		return $this->createJsonResponse($mySharingContacts);
	}

	/**
	 * Show the form to share my files to other users. The most recent 5 contacts will be displayed in the form by default.
	 * @param Request $request HTTP request
	 * @return \Symfony\Component\HttpFoundation\Response the for the share files
	 */
	public function showShareFormAction(Request $request) {
		$currentUser = $this->getCurrentUser ();
		if (!$currentUser->isTeacher() && !$currentUser->isAdmin()) {
			throw $this->createAccessDeniedException('您无权访问此页面');
		}

		$currentUserId = $currentUser['id'];
		
		$allTeachers =  $this->getUserService()->searchUsers(array('roles'=> 'ROLE_TEACHER', 'locked'=>0), array('nickname', 'ASC'), 0, 1000);
		
		return $this->render ( 'MaterialLibBundle:MaterialLib:share-my-materials.html.twig', array (
				'allTeachers' => $allTeachers,
				'currentUserId' => $currentUserId
		) );
	}
	
	/**
	 * Show the sharing history list. The user can then deactivate the sharing records.
	 * @param Request $request HTTP request
	 * @return \Symfony\Component\HttpFoundation\Response HTTP response
	 */
	public function showShareHistoryAction(Request $request)
	{
		$user = $this->getCurrentUser ();
		if (!$user->isTeacher() && !$user->isAdmin()) {
			throw $this->createAccessDeniedException('您无权访问此页面');
		}

		$shareHistories = $this->getUploadFileService()->findShareHistory($user['id']);
		
		$targetUserIds = array();
		
		if(!empty($shareHistories)){
			foreach ($shareHistories as $history){
				array_push($targetUserIds, $history['targetUserId']);
			}
			
			$targetUsers = $this->getUserService()->findUsersByIds($targetUserIds);
		}
		
		return $this->render ( 'MaterialLibBundle:MaterialLib:material-share-history.html.twig', array (
				'shareHistories' => $shareHistories,
				'targetUsers' => isset($targetUsers) ? $targetUsers : array(),
				'source' => 'myShareHistory'
		) );
	}
	
	/**
	 * Save the sharing settings. If a previous sharing record exists, then update it. Otherwise create a new record.
	 * @param Request $request HTTP request
	 * @return \Symfony\Component\HttpFoundation\JsonResponse JSON response
	 */
	public function saveShareAction(Request $request){
		$currentUser = $this->getCurrentUser ();
		if (!$currentUser->isTeacher() && !$currentUser->isAdmin()) {
			throw $this->createAccessDeniedException('您无权访问此页面');
		}

		$currentUserId = $currentUser['id'];
		$targetUserIds = $request->get('targetUserIds');
		
		if (!empty($targetUserIds)) {
			foreach ( $targetUserIds as $targetUserId ) {
				if ($targetUserId != $currentUserId) {
					$shareHistory = $this->getUploadFileService()->findShareHistoryByUserId($currentUserId, $targetUserId);
				
					if (isset($shareHistory)) {
						$this->getUploadFileService()->updateShare($shareHistory['id']);
					} else {
						$this->getUploadFileService()->addShare($currentUserId, $targetUserId);
					}

					$targetUser = $this->getUserService()->getUser($targetUserId);
					$userUrl = $this->generateUrl('user_show', array('id' => $currentUser['id']), true);
					$toMyShareUrl = $this->generateUrl('material_lib_browsing', array('type'=>'all', 'viewMode'=>'thumb', 'source'=>'shared'));
					$this->getNotificationService()->notify($targetUser['id'], 'default', "<a href='{$userUrl}' target='_blank'><strong>{$currentUser['nickname']}</strong></a>已将资料分享给你，<a href='{$toMyShareUrl}'>点击查看</a>");
				}
			}
		}

		return $this->createJsonResponse(true);
	}
	
	/**
	 * Deactivate a sharing record. This will only set a flag in the database table. 
	 * The record will not be deleted. This is for the purpose to maintain a complete sharing history.
	 * @param Request $request HTTP request
	 * @return \Symfony\Component\HttpFoundation\JsonResponse JSON response
	 */
	public function cancelShareAction(Request $request){
		$currentUser = $this->getCurrentUser ();
		if (!$currentUser->isTeacher() && !$currentUser->isAdmin()) {
			throw $this->createAccessDeniedException('您无权访问此页面');
		}

		$currentUserId = $currentUser['id'];
		$targetUserId = $request->get('targetUserId' );
		
		if(!empty($targetUserId)){
			$this->getUploadFileService()->cancelShareFile($currentUserId, $targetUserId);

			$targetUser = $this->getUserService()->getUser($targetUserId);
			$userUrl = $this->generateUrl('user_show', array('id' => $currentUser['id']), true);
			$toMyShareUrl = $this->generateUrl('material_lib_browsing', array('type'=>'all', 'viewMode'=>'thumb', 'source'=>'shared'));
        	$this->getNotificationService()->notify($targetUser['id'], 'default', "<a href='{$userUrl}' target='_blank'><strong>{$currentUser['nickname']}</strong></a>已取消分享资料给你，<a href='{$toMyShareUrl}'>点击查看</a>");
		}
		
		return $this->createJsonResponse ( true );
	}

	/**
	 * Preview a file in a pop-up window.
	 * @param Request $request HTTP request
	 * @param unknown $fileId file id
	 * @return \Symfony\Component\HttpFoundation\Response HTTP response
	 */
	public function previewAction(Request $request, $fileId) {
		$user = $this->getCurrentUser ();
		if (!$user->isTeacher() && !$user->isAdmin()) {
			throw $this->createAccessDeniedException('您无权访问此页面');
		}

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

					$token = $this->getTokenService()->makeToken('hls.playlist', array('data' => $file['id'], 'times' => 3, 'duration' => 3600));
					
					$hls = array(
                        'url' => $this->generateUrl('hls_playlist', array(
                            'id' => $file['id'], 
                            'token' => $token['token'],
                            'line' => $request->query->get('line')
                        ), true)
                    );
				} else {
					$hls = $client->generateHLSQualitiyListUrl ( $file ['metas2'], 3600 );
				}
			}
		}

		return $this->render ( 'MaterialLibBundle:MaterialLib:preview-modal.html.twig', array (
				'user' => $user,
				'file' => $file,
				'hlsUrl' => (isset ( $hls ) and is_array($hls) and !empty($hls['url'])) ? $hls['url'] : '',
		));
	}
	
	/**
	 * Generate HLS key which will be used when preview a file from the cloud.
	 * @param Request $request HTTP request
	 * @param unknown $fileId file id
	 * @param unknown $token token
	 * @return \Symfony\Component\HttpFoundation\Response HTTP response
	 */
	public function hlskeyurlAction(Request $request, $fileId, $token)
	{
		$user = $this->getCurrentUser ();
		if (!$user->isTeacher() && !$user->isAdmin()) {
			throw $this->createAccessDeniedException('您无权访问此页面');
		}

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
	
	/**
	 * Preview a PPT file.
	 * @param Request $request HTTP request
	 * @param unknown $fileId file id
	 * @return \Symfony\Component\HttpFoundation\JsonResponse JSON response
	 */
	public function pptAction(Request $request, $fileId)
	{
		$user = $this->getCurrentUser ();
		if (!$user->isTeacher() && !$user->isAdmin()) {
			throw $this->createAccessDeniedException('您无权访问此页面');
		}

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

	private function getHeadLeaderInfo()
	{
		$storage = $this->getSettingService()->get("storage");
		if(!empty($storage) && array_key_exists("video_header", $storage) && $storage["video_header"]){
	
			$headLeader = $this->getUploadFileService()->getFileByTargetType('headLeader');
			$headLeaderArray = json_decode($headLeader['metas2'],true);
			$headLeaders = array();
			foreach ($headLeaderArray as $key => $value) {
				$headLeaders[$key] = $value['key'];
			}
			$headLeaderHlsKeyUrl = $this->generateUrl('uploadfile_cloud_get_head_leader_hlskey', array(), true);
	
			return array(
					'headLeaders' => $headLeaders,
					'headLeaderHlsKeyUrl' => $headLeaderHlsKeyUrl,
					'headLength' => $headLeader['length']
			);
		} else {
			return array(
					'headLeaders' => '',
					'headLeaderHlsKeyUrl' => '',
					'headLength' => 0
			);
		}
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
	
	private function getTokenService()
	{
		return $this->getServiceKernel()->createService('User.TokenService');
	}

	private function getNotificationService()
    {
        return $this->getServiceKernel()->createService('User.NotificationService');
    }
}