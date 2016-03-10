<?php

namespace MaterialLib\MaterialLibBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Util\CloudClientFactory;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\CloudPlatform\CloudAPIFactory;

class MaterialLibController extends BaseController
{
    /**
     * Browse material lib. Use can switch between file types and search by keywords.
     * @param  Request                                    $request  HTTP request
     * @param  string                                     $type     file type (values: video|audio|document|image|ppt|other)
     * @param  string                                     $viewMode viewing mode (values: thumb|list)
     * @param  string                                     $source   source of the file (values: upload|shared)
     * @return \Symfony\Component\HttpFoundation\Response the HTTP response
     */
    public function indexAction(Request $request, $type = "all", $viewMode = "thumb", $source = "upload")
    {
        $currentUser = $this->getCurrentUser();

        if (!$currentUser->isTeacher() && !$currentUser->isAdmin()) {
            throw $this->createAccessDeniedException('您无权访问此页面');
        }

        $currentUserId = $currentUser['id'];
        $data          = $request->query->all();

        $keyWord = $request->query->get('keyword') ?: "";

        $conditions           = array();
        $conditions['status'] = 'ok';

        if ($type != 'all') {
            $conditions['type'] = $type;
        }

        if (!empty($keyWord)) {
            $conditions['filename'] = $keyWord;
        }

        $conditions['source']        = $source;
        $conditions['currentUserId'] = $currentUserId;

        $paginator = new Paginator($request, $this->getUploadFileService()->searchFilesCount($conditions), 20);

        $files = $this->getUploadFileService()->searchFiles($conditions, array('createdTime', 'DESC'), $paginator->getOffsetCount(), $paginator->getPerPageCount());

        $createdUsers = $this->getUserService()->findUsersByIds(ArrayToolkit::column($files, 'createdUserId'));

        //Return different views according to current viewing mode

        if ($viewMode == 'thumb') {
            $resultPage = 'MaterialLibBundle:MaterialLib:material-thumb-view.html.twig';
        } else {
            $resultPage = 'MaterialLibBundle:MaterialLib:material-list-view.html.twig';
        }

        $storageSetting = $this->getSettingService()->get("storage");

        $tags = $this->getTagService()->findAllTags(0, 999);

        return $this->render($resultPage, array(
            'currentUserId'  => $currentUserId,
            'type'           => $type,
            'files'          => $files,
            'createdUsers'   => $createdUsers,
            'paginator'      => $paginator,
            'storageSetting' => $storageSetting,
            'viewMode'       => $viewMode,
            'source'         => $source,
            'now'            => time(),
            'tags'           => $tags
        ));
    }

    public function showMyMaterialLibFormAction(Request $request, $viewMode = "thumb")
    {
        $currentUser = $this->getCurrentUser();

        if (!$currentUser->isTeacher() && !$currentUser->isAdmin()) {
            throw $this->createAccessDeniedException('您无权访问此页面');
        }
        $currentUserId = $currentUser['id'];
        $data          = $request->query->all();
        $source        = $data['source'];
        $type          = $data['type'];
        $keyWord       = $request->query->get('keyword') ?: "";

        $conditions           = array();
        $conditions['status'] = 'ok';

        if ($type != 'all') {
            $conditions['type'] = $type;
        }

        if (!empty($keyWord)) {
            $conditions['filename'] = $keyWord;
        }

        $conditions['source']        = $source;
        $conditions['currentUserId'] = $currentUserId;

        $paginator = new Paginator($request, $this->getUploadFileService()->searchFilesCount($conditions), 10);

        $files = $this->getUploadFileService()->searchFiles($conditions, array('createdTime', 'DESC'), $paginator->getOffsetCount(), $paginator->getPerPageCount());

        $createdUsers = $this->getUserService()->findUsersByIds(ArrayToolkit::column($files, 'createdUserId'));

        //Return different views according to current viewing mode

        if ($viewMode == 'thumb') {
            $resultPage = 'MaterialLibBundle:Web/Widget:thumb-list.html.twig';
        } else {
            $resultPage = 'MaterialLibBundle:MaterialLib:material-list-view-item.html.twig';
        }

        $storageSetting = $this->getSettingService()->get("storage");

        $tags = $this->getTagService()->findAllTags(0, 999);

        return $this->render($resultPage, array(
            'currentUserId'  => $currentUserId,
            'type'           => $type,
            'files'          => $files,
            'createdUsers'   => $createdUsers,
            'paginator'      => $paginator,
            'storageSetting' => $storageSetting,
            'viewMode'       => $viewMode,
            'source'         => $source,
            'now'            => time(),
            'tags'           => $tags
        ));
    }

    public function deleteAction(Request $request, $id)
    {
        $file   = $this->tryAccessFile($id);
        $status = $this->getUploadFileService()->deleteFiles(array($id));
        try {
            $api = CloudAPIFactory::create('leaf');
            $api->setApiUrl("http://andytest.edusoho.net:8081");
            $result = $api->delete("/resources/{$file['globalId']}");
        } catch (\RuntimeException $e) {
            return $this->createJsonResponse(false);
        }
        if (isset($result['success']) && $result['success'] == true) {
            return $this->createJsonResponse(true);
        } else {
            return $this->createJsonResponse(false);
        }
    }

    //需要改成调用云平台群删的接口
    public function deletesAction(Request $request)
    {
        $ids = $request->request->get('ids');

        foreach ($ids as $id) {
            $file = $this->tryAccessFile($id);
            $this->getUploadFileService()->deleteFiles(array($id));
        }

        return $this->createJsonResponse(true);
    }

    /**
     * Get the users who is sharing material lib to current user. Inactive sharing contacts will not be included in the results.
     * @param  Request                                        $request HTTP request
     * @return \Symfony\Component\HttpFoundation\JsonResponse JSON response
     */
    public function findMySharingContactsAction(Request $request)
    {
        $user = $this->getCurrentUser();

        if (!$user->isTeacher() && !$user->isAdmin()) {
            throw $this->createAccessDeniedException('您无权访问此页面');
        }

        $mySharingContacts = $this->getUploadFileService()->findMySharingContacts($user['id']);

        return $this->createJsonResponse($mySharingContacts);
    }

    /**
     * Show the form to share my files to other users. The most recent 5 contacts will be displayed in the form by default.
     * @param  Request                                    $request HTTP request
     * @return \Symfony\Component\HttpFoundation\Response the for the share files
     */
    public function showShareFormAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();

        if (!$currentUser->isTeacher() && !$currentUser->isAdmin()) {
            throw $this->createAccessDeniedException('您无权访问此页面');
        }

        $currentUserId = $currentUser['id'];

        $allTeachers = $this->getUserService()->searchUsers(array('roles' => 'ROLE_TEACHER', 'locked' => 0), array('nickname', 'ASC'), 0, 1000);

        return $this->render('MaterialLibBundle:MaterialLib:share-my-materials.html.twig', array(
            'allTeachers'   => $allTeachers,
            'currentUserId' => $currentUserId
        ));
    }

    /**
     * Show the sharing history list. The user can then deactivate the sharing records.
     * @param  Request                                    $request HTTP request
     * @return \Symfony\Component\HttpFoundation\Response HTTP response
     */
    public function showShareHistoryAction(Request $request)
    {
        $user = $this->getCurrentUser();

        if (!$user->isTeacher() && !$user->isAdmin()) {
            throw $this->createAccessDeniedException('您无权访问此页面');
        }

        $shareHistories = $this->getUploadFileService()->findShareHistory($user['id']);

        $targetUserIds = array();

        if (!empty($shareHistories)) {
            foreach ($shareHistories as $history) {
                array_push($targetUserIds, $history['targetUserId']);
            }

            $targetUsers = $this->getUserService()->findUsersByIds($targetUserIds);
        }

        return $this->render('MaterialLibBundle:MaterialLib:material-share-history.html.twig', array(
            'shareHistories' => $shareHistories,
            'targetUsers'    => isset($targetUsers) ? $targetUsers : array(),
            'source'         => 'myShareHistory'
        ));
    }

    /**
     * Save the sharing settings. If a previous sharing record exists, then update it. Otherwise create a new record.
     * @param  Request                                        $request HTTP request
     * @return \Symfony\Component\HttpFoundation\JsonResponse JSON response
     */
    public function saveShareAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();

        if (!$currentUser->isTeacher() && !$currentUser->isAdmin()) {
            throw $this->createAccessDeniedException('您无权访问此页面');
        }

        $currentUserId = $currentUser['id'];
        $targetUserIds = $request->get('targetUserIds');

        if (!empty($targetUserIds)) {
            foreach ($targetUserIds as $targetUserId) {
                if ($targetUserId != $currentUserId) {
                    $shareHistory = $this->getUploadFileService()->findShareHistoryByUserId($currentUserId, $targetUserId);

                    if (isset($shareHistory)) {
                        $this->getUploadFileService()->updateShare($shareHistory['id']);
                    } else {
                        $this->getUploadFileService()->addShare($currentUserId, $targetUserId);
                    }

                    $targetUser   = $this->getUserService()->getUser($targetUserId);
                    $userUrl      = $this->generateUrl('user_show', array('id' => $currentUser['id']), true);
                    $toMyShareUrl = $this->generateUrl('material_lib_browsing', array('type' => 'all', 'viewMode' => 'thumb', 'source' => 'shared'));
                    $this->getNotificationService()->notify($targetUser['id'], 'default', "<a href='{$userUrl}' target='_blank'><strong>{$currentUser['nickname']}</strong></a>已将资料分享给你，<a href='{$toMyShareUrl}'>点击查看</a>");
                }
            }
        }

        return $this->createJsonResponse(true);
    }

    /**
     * Deactivate a sharing record. This will only set a flag in the database table.
     * The record will not be deleted. This is for the purpose to maintain a complete sharing history.
     * @param  Request                                        $request HTTP request
     * @return \Symfony\Component\HttpFoundation\JsonResponse JSON response
     */
    public function cancelShareAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();

        if (!$currentUser->isTeacher() && !$currentUser->isAdmin()) {
            throw $this->createAccessDeniedException('您无权访问此页面');
        }

        $currentUserId = $currentUser['id'];
        $targetUserId  = $request->get('targetUserId');

        if (!empty($targetUserId)) {
            $this->getUploadFileService()->cancelShareFile($currentUserId, $targetUserId);

            $targetUser   = $this->getUserService()->getUser($targetUserId);
            $userUrl      = $this->generateUrl('user_show', array('id' => $currentUser['id']), true);
            $toMyShareUrl = $this->generateUrl('material_lib_browsing', array('type' => 'all', 'viewMode' => 'thumb', 'source' => 'shared'));
            $this->getNotificationService()->notify($targetUser['id'], 'default', "<a href='{$userUrl}' target='_blank'><strong>{$currentUser['nickname']}</strong></a>已取消分享资料给你，<a href='{$toMyShareUrl}'>点击查看</a>");
        }

        return $this->createJsonResponse(true);
    }

    public function previewAction(Request $request, $fileId)
    {
        $file = $this->tryAccessFile($fileId);

        return $this->render('MaterialLibBundle:MaterialLib:preview-modal.html.twig', array(
            'file' => $file
        ));
    }

    public function downloadAction(Request $request, $id)
    {
        $file = $this->tryAccessFile($id);
        return $this->forward('TopxiaWebBundle:FileWatch:download', array('file' => $file));
    }

    protected function tryAccessFile($fileId)
    {
        $file = $this->getUploadFileService()->getFile($fileId);

        if (empty($file)) {
            throw $this->createNotFoundException();
        }

        $user = $this->getCurrentUser();

        if ($user->isAdmin()) {
            return $file;
        }

        if (!$user->isTeacher()) {
            throw $this->createAccessDeniedException('您无权访问此文件！');
        }

        if ($file['createdUserId'] == $user['id']) {
            return $file;
        }

        $shares = $this->getUploadFileService()->findShareHistory($file['createdUserId']);

        foreach ($shares as $share) {
            if ($share['targetUserId'] == $user['id']) {
                return $file;
            }
        }

        throw $this->createAccessDeniedException('您无权访问此文件！');
    }

    //加载播放器的地址
    public function playerAction(Request $request, $fileId)
    {
        $file = $this->getUploadFileService()->getFile($fileId);
        if (!empty($file)) {
            try {
                $api = CloudAPIFactory::create('leaf');
                $api->setApiUrl("http://andytest.edusoho.net:8081");
                $result = $api->get("/resources/{$file['globalId']}");
            } catch (\RuntimeException $e) {
                return $this->render('TopxiaAdminBundle:EduCloud:api-error.html.twig', array());
            }
        }
        $file = $this->tryAccessFile($fileId);
        $url  = $this->generateUrl("material_lib_file_play_url", array(
            'fileId' => $fileId
        ), true);
        return $this->forward('TopxiaWebBundle:Player:show', array(
            'id'  => $fileId,
            'url' => $url
        ));
    }

    /**
     * Generate HLS key which will be used when preview a file from the cloud.
     * @param  Request                                    $request HTTP request
     * @param  unknown                                    $fileId  file id
     * @param  unknown                                    $token   token
     * @return \Symfony\Component\HttpFoundation\Response HTTP response
     */
    public function hlskeyurlAction(Request $request, $fileId, $token)
    {
        $user = $this->getCurrentUser();

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
     * @param  Request                                        $request HTTP request
     * @param  unknown                                        $fileId  file id
     * @return \Symfony\Component\HttpFoundation\JsonResponse JSON response
     */
    public function pptAction(Request $request, $fileId)
    {
        $user = $this->getCurrentUser();

        if (!$user->isTeacher() && !$user->isAdmin()) {
            throw $this->createAccessDeniedException('您无权访问此页面');
        }

        $file = $this->getUploadFileService()->getFile($fileId);

        if (empty($file) || $file['type'] != 'ppt') {
            throw $this->createNotFoundException();
        }

        if (!empty($file['globalId'])) {
            $file = $this->getServiceKernel()->createService('File.UploadFileService2')->getFile($fileId);
        }

        if ($file['convertStatus'] != 'success') {
            if ($file['convertStatus'] == 'error') {
                $message = sprintf('PPT文档转换失败，请重新转换。');
                return $this->createJsonResponse(array(
                    'error' => array('code' => 'error', 'message' => $message)
                ));
            } else {
                return $this->createJsonResponse(array(
                    'error' => array('code' => 'processing', 'message' => 'PPT文档还在转换中，还不能查看，请稍等。')
                ));
            }
        }

        $factory = new CloudClientFactory();
        $client  = $factory->createClient();

        $result = $client->pptImages($file['metas2']['imagePrefix'], $file['metas2']['length'].'');

        return $this->createJsonResponse($result);
    }

    public function contentAction(Request $request, $fileId)
    {
        $file = $this->tryAccessFile($fileId);
        return $this->forward('TopxiaWebBundle:CourseLesson:file', array('fileId' => $file['id'], 'isDownload' => true));
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File.UploadFileService2');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getTagService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.TagService');
    }

    protected function getNotificationService()
    {
        return $this->getServiceKernel()->createService('User.NotificationService');
    }
}
