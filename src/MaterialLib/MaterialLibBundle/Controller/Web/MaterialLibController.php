<?php

namespace MaterialLib\MaterialLibBundle\Controller\Web;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use MaterialLib\MaterialLibBundle\Controller\BaseController;

class MaterialLibController extends BaseController
{
    public function indexAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();

        if (!$currentUser->isTeacher() && !$currentUser->isAdmin()) {
            throw $this->createAccessDeniedException('您无权访问此页面');
        }

        return $this->render('MaterialLibBundle:Web:material-thumb-view.html.twig', array(
            'tags' => $this->getTagService()->findAllTags(0, PHP_INT_MAX)
        ));
    }

    public function matchAction(Request $request)
    {
        $data        = array();
        $queryString = $request->query->get('q');
        $tags        = $this->getTagService()->getTagByLikeName($queryString);

        foreach ($tags as $tag) {
            $data[] = array('id' => $tag['id'], 'name' => $tag['name']);
        }

        return $this->createJsonResponse($data);
    }

    public function showMyMaterialLibFormAction(Request $request)
    {
        //$synData = $this->getMaterialLibService()->synData();

        $currentUser = $this->getCurrentUser();

        if (!$currentUser->isTeacher() && !$currentUser->isAdmin()) {
            throw $this->createAccessDeniedException('您无权访问此页面');
        }

        $currentUserId        = $currentUser['id'];
        $conditions           = $request->query->all();
        $conditions['status'] = 'ok';

        if (!empty($conditions['keyword'])) {
            $conditions['filename'] = $conditions['keyword'];
            unset($conditions['keyword']);
        }

        $conditions['currentUserId'] = $currentUserId;
        $paginator                   = new Paginator(
            $request,
            $this->getUploadFileService()->searchFilesCount($conditions),
            20
        );

        $files = $this->getUploadFileService()->searchFiles(
            $conditions,
            array('createdTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $collections = $this->getUploadFileService()->findCollectionsByUserIdAndFileIds(
            ArrayToolkit::column($files, 'id'),
            $currentUserId
        );

        $collections = ArrayToolkit::index($collections, 'fileId');

        $createdUsers = $this->getUserService()->findUsersByIds(ArrayToolkit::column($files, 'createdUserId'));

        return $this->render('MaterialLibBundle:Web/Widget:thumb-list.html.twig', array(
            'files'        => $files,
            'collections'  => $collections,
            'createdUsers' => $createdUsers,
            'paginator'    => $paginator
        ));
    }

    public function previewAction(Request $request, $fileId)
    {
        $file = $this->tryAccessFile($fileId);
        return $this->render('MaterialLibBundle:Web:preview-modal.html.twig', array(
            'file' => $file
        ));
    }

    public function playerAction(Request $request, $fileId)
    {
        $file = $this->tryAccessFile($fileId);

        return $this->forward('MaterialLibBundle:GlobalFilePlayer:player', array(
            'globalId' => $file['globalId']
        ));
    }

    public function editAction(Request $request, $globalId)
    {
        $fields = $request->request->all();

        $this->getMaterialLibService()->edit($globalId, $fields);
        return $this->createJsonResponse(array('success' => true));
    }

    public function reconvertAction($globalId)
    {
        $this->getMaterialLibService()->reconvert($globalId);
        return $this->createJsonResponse(array('success' => true));
    }

    public function detailAction($globalId)
    {
        $currentUser = $this->getCurrentUser();
        $file        = $this->getUploadFileService()->getFileByGlobalId($globalId);

        if (!($file['createdUserId'] == $currentUser['id'])) {
            $material = $this->getMaterialLibService()->get($globalId);
            return $this->render('MaterialLibBundle:Web:static-detail.html.twig', array(
                'material'   => $material,
                'thumbnails' => empty($thumbnails) ? "" : $thumbnails
            ));
        }

        return $this->forward('TopxiaAdminBundle:CloudFile:detail', array('globalId' => $globalId));
    }

    public function showShareFormAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();

        if (!$currentUser->isTeacher() && !$currentUser->isAdmin()) {
            throw $this->createAccessDeniedException('您无权访问此页面');
        }

        $currentUserId = $currentUser['id'];

        $allTeachers = $this->getUserService()->searchUsers(array('roles' => 'ROLE_TEACHER', 'locked' => 0), array('nickname', 'ASC'), 0, 1000);

        return $this->render('MaterialLibBundle:Web/MyShare:share-my-materials.html.twig', array(
            'allTeachers'   => $allTeachers,
            'currentUserId' => $currentUserId
        ));
    }

    public function showShareHistoryAction(Request $request)
    {
        $user = $this->getCurrentUser();

        if (!$user->isTeacher() && !$user->isAdmin()) {
            throw $this->createAccessDeniedException('您无权访问此页面');
        }

        $conditions['sourceUserId'] = $user['id'];

        $conditions['isActive'] = 1;
        $shareHistoryCount      = $this->getUploadFileService()->searchShareHistoryCount($conditions);

        $paginator = new Paginator(
            $this->get('request'),
            $shareHistoryCount,
            10
        );

        $shareHistories = $this->getUploadFileService()->searchShareHistories(
            $conditions,
            array('updatedTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $targetUserIds = array();

        if (!empty($shareHistories)) {
            foreach ($shareHistories as $history) {
                array_push($targetUserIds, $history['targetUserId']);
            }

            $targetUsers = $this->getUserService()->findUsersByIds($targetUserIds);
        }

        $allTeachers = $this->getUserService()->searchUsers(array('roles' => 'ROLE_TEACHER', 'locked' => 0), array('nickname', 'ASC'), 0, 1000);
        return $this->render('MaterialLibBundle:Web/MyShare:material-share-history.html.twig', array(
            'shareHistories' => isset($shareHistories) ? $shareHistories : array(),
            'targetUsers'    => isset($targetUsers) ? $targetUsers : array(),
            'source'         => 'myShareHistory',
            'currentUserId'  => $user['id'],
            'allTeachers'    => $allTeachers,
            'paginator'      => $paginator
        ));
    }

    public function historyUserShowAction(Request $request)
    {
        $user = $this->getCurrentUser();

        $conditions['sourceUserId'] = $user['id'];

        $conditions['isActive'] = 1;
        $shareHistoryCount      = $this->getUploadFileService()->searchShareHistoryCount($conditions);

        $paginator = new Paginator(
            $this->get('request'),
            $shareHistoryCount,
            10
        );

        $shareHistories = $this->getUploadFileService()->searchShareHistories(
            $conditions,
            array('updatedTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $targetUserIds = array();

        if (!empty($shareHistories)) {
            foreach ($shareHistories as $history) {
                array_push($targetUserIds, $history['targetUserId']);
            }

            $targetUsers = $this->getUserService()->findUsersByIds($targetUserIds);
        }

        return $this->render('MaterialLibBundle:Web/MyShare:material-share-history-users.html.twig', array(
            'shareHistories' => isset($shareHistories) ? $shareHistories : array(),
            'targetUsers'    => isset($targetUsers) ? $targetUsers : array(),
            'source'         => 'myShareHistory',
            'currentUserId'  => $user['id'],
            'paginator'      => $paginator
        ));
    }

    public function historyDetailShowAction(Request $request)
    {
        $user                       = $this->getCurrentUser();
        $conditions['sourceUserId'] = $user['id'];

        $shareHistoryCount = $this->getUploadFileShareHistoryService()->searchShareHistoryCount($conditions);

        $paginator = new Paginator(
            $this->get('request'),
            $shareHistoryCount,
            10
        );

        $shareHistories = $this->getUploadFileShareHistoryService()->searchShareHistories(
            $conditions,
            array('createdTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $targetUserIds = array();

        if (!empty($shareHistories)) {
            foreach ($shareHistories as $history) {
                array_push($targetUserIds, $history['targetUserId']);
            }

            $targetUsers = $this->getUserService()->findUsersByIds($targetUserIds);
        }

        return $this->render('MaterialLibBundle:Web/MyShare:material-share-history-detail.html.twig', array(
            'shareHistories' => isset($shareHistories) ? $shareHistories : array(),
            'targetUsers'    => isset($targetUsers) ? $targetUsers : array(),
            'source'         => 'myShareHistory',
            'currentUserId'  => $user['id'],
            'paginator'      => $paginator
        ));
    }

    public function findMySharingContactsAction(Request $request)
    {
        $user = $this->getCurrentUser();

        if (!$user->isTeacher() && !$user->isAdmin()) {
            throw $this->createAccessDeniedException('您无权访问此页面');
        }

        $mySharingContacts = $this->getUploadFileService()->findMySharingContacts($user['id']);

        return $this->createJsonResponse($mySharingContacts);
    }

    public function saveShareAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();

        if (!$currentUser->isTeacher() && !$currentUser->isAdmin()) {
            throw $this->createAccessDeniedException('您无权访问此页面');
        }

        $currentUserId = $currentUser['id'];
        $targetUserIds = $request->request->get('targetUserIds');

        if (!empty($targetUserIds)) {
            foreach ($targetUserIds as $targetUserId) {
                if ($targetUserId != $currentUserId) {
                    $shareHistory = $this->getUploadFileService()->findShareHistoryByUserId($currentUserId, $targetUserId);

                    if (isset($shareHistory)) {
                        $this->getUploadFileService()->updateShare($shareHistory['id']);
                    } else {
                        $this->getUploadFileService()->addShare($currentUserId, $targetUserId);
                    }

                    $this->getUploadFileShareHistoryService()->addShareHistory($currentUserId, $targetUserId, 1);

                    $targetUser   = $this->getUserService()->getUser($targetUserId);
                    $userUrl      = $this->generateUrl('user_show', array('id' => $currentUser['id']), true);
                    $toMyShareUrl = $this->generateUrl('material_lib_browsing', array('type' => 'all', 'viewMode' => 'thumb', 'source' => 'shared'));
                    $this->getNotificationService()->notify($targetUser['id'], 'default', "<a href='{$userUrl}' target='_blank'><strong>{$currentUser['nickname']}</strong></a>已将资料分享给你，<a href='{$toMyShareUrl}'>点击查看</a>");
                }
            }
        }

        return $this->createJsonResponse(true);
    }

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
            $this->getUploadFileShareHistoryService()->addShareHistory($currentUserId, $targetUserId, 0);

            $targetUser   = $this->getUserService()->getUser($targetUserId);
            $userUrl      = $this->generateUrl('user_show', array('id' => $currentUser['id']), true);
            $toMyShareUrl = $this->generateUrl('material_lib_browsing', array('type' => 'all', 'viewMode' => 'thumb', 'source' => 'shared'));
            $this->getNotificationService()->notify($targetUser['id'], 'default', "<a href='{$userUrl}' target='_blank'><strong>{$currentUser['nickname']}</strong></a>已取消分享资料给你，<a href='{$toMyShareUrl}'>点击查看</a>");
        }

        return $this->createJsonResponse(true);
    }

    public function collectAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $data = $request->query->all();

        $collection = $this->getUploadFileService()->collectFile($user['id'], $data['fileId']);

        if (empty($collection)) {
            return $this->createJsonResponse(false);
        }

        return $this->createJsonResponse(true);
    }

    public function downloadAction($globalId)
    {
        $download = $this->getMaterialLibService()->download($globalId);
        return $this->redirect($download['url']);
    }

    public function deleteAction($globalId)
    {
        $result = $this->getMaterialLibService()->delete($globalId);
        return $this->createJsonResponse($result);
    }

    public function batchDeleteAction(Request $request)
    {
        $data = $request->request->all();

        if (isset($data['globalIds']) && $data['globalIds'] != "") {
            $result = $this->getMaterialLibService()->batchDelete($data['globalIds']);
            return $this->createJsonResponse($result);
        }

        return $this->createJsonResponse(false);
    }

    public function batchShareAction(Request $request)
    {
        $data = $request->request->all();

        if (isset($data['globalIds']) && $data['globalIds'] != "") {
            $result = $this->getMaterialLibService()->batchShare($data['globalIds']);
            return $this->createJsonResponse($result);
        }

        return $this->createJsonResponse(false);
    }

    public function batchTagShowAction(Request $request)
    {
        $data    = $request->request->all();
        $fileIds = preg_split('/,/', $data['fileIds']);

        foreach ($fileIds as $key => $fileId) {
            $file = $this->getUploadFileService()->getFile($fileId);

            if (!empty($file['globalId'])) {
                $this->getMaterialLibService()->edit($file['globalId'], array('tags' => $data['tags']));
            } else {
                continue;
            }
        }

        return $this->redirect($this->generateUrl('material_lib_browsing'));
    }

    public function generateThumbnailAction(Request $request, $globalId)
    {
        $second = $request->query->get('second');
        return $this->createJsonResponse($this->getMaterialLibService()->getThumbnail($globalId, array('seconds' => $second)));
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

        if ($file['isPublic'] == 1) {
            return $file;
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

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getMaterialLibService()
    {
        return $this->createService('MaterialLib:MaterialLib.MaterialLibService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getTagService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.TagService');
    }

    protected function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File.UploadFileService2');
    }

    protected function getUploadFileTagService()
    {
        return $this->getServiceKernel()->createService('File.UploadFileTagService');
    }

    protected function getUploadFileShareHistoryService()
    {
        return $this->getServiceKernel()->createService('File.UploadFileShareHistoryService');
    }

    protected function getNotificationService()
    {
        return $this->getServiceKernel()->createService('User.NotificationService');
    }
}
