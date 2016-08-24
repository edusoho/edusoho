<?php

namespace MaterialLib\MaterialLibBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\BaseController;

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
        $data = array();

        $currentUser = $this->getCurrentUser();

        if (!$currentUser->isTeacher() && !$currentUser->isAdmin()) {
            return $this->createJsonResponse($data);
        }

        $queryString = $request->query->get('q');
        $tags        = $this->getTagService()->getTagByLikeName($queryString);

        foreach ($tags as $tag) {
            $data[] = array('id' => $tag['id'], 'name' => $tag['name']);
        }

        return $this->createJsonResponse($data);
    }

    public function showMyMaterialLibFormAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();

        if (!$currentUser->isTeacher() && !$currentUser->isAdmin()) {
            throw $this->createAccessDeniedException('您无权访问此页面');
        }

        $currentUserId              = $currentUser['id'];
        $conditions                 = $request->query->all();
        $source                     = $conditions['sourceFrom'];
        $conditions['status']       = 'ok';
        $conditions['noTargetType'] = 'attachment';
        if (!empty($conditions['keyword'])) {
            $conditions['filename'] = $conditions['keyword'];
            unset($conditions['keyword']);
        }

        $conditions['currentUserId'] = $currentUserId;

        $paginator = new Paginator(
            $request,
            $this->getUploadFileService()->searchFileCount($conditions),
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
        $createdUsers = ArrayToolkit::index($createdUsers, 'id');

        return $this->render('MaterialLibBundle:Web/Widget:thumb-list.html.twig', array(
            'files'        => $files,
            'collections'  => $collections,
            'createdUsers' => $createdUsers,
            'source'       => $source,
            'paginator'    => $paginator
        ));
    }

    public function previewAction(Request $request, $fileId)
    {
        $file = $this->getUploadFileService()->tryAccessFile($fileId);
        if ($file['storage'] == 'cloud') {
            return $this->forward('TopxiaAdminBundle:CloudFile:preview', array(
                'request'  => $request,
                'globalId' => $file['globalId']
            ));
        }

        return $this->render('MaterialLibBundle:Web:preview.html.twig', array(
            'file' => $file
        ));
    }

    public function playerAction(Request $request, $fileId)
    {
        $file = $this->getUploadFileService()->tryAccessFile($fileId);

        if ($file['storage'] == 'cloud') {
            return $this->forward('MaterialLibBundle:GlobalFilePlayer:player', array(
                'request'  => $request,
                'globalId' => $file['globalId']
            ));
        }
        return $this->render('MaterialLibBundle:Web:local-player.html.twig', array());
    }

    public function reconvertAction($globalId)
    {
        $this->getUploadFileService()->tryManageGlobalFile($globalId);

        $uploadFile = $this->getMaterialLibService()->reconvert($globalId);

        return $this->render('MaterialLibBundle:Web/Widget:thumb-item.html.twig', array(
            'uploadFile' => $uploadFile
        ));
    }

    public function detailAction(Request $request, $fileId)
    {
        $currentUser = $this->getCurrentUser();
        $file        = $this->getUploadFileService()->tryAccessFile($fileId);

        if ($file['storage'] == 'local' || $currentUser['id'] != $file['createdUserId']) {
            $fileTags     = $this->getUploadFileTagService()->findByFileId($fileId);
            $tags         = $this->getTagService()->findTagsByIds(ArrayToolkit::column($fileTags, 'tagId'));
            $file['tags'] = ArrayToolkit::column($tags, 'name');
            return $this->render('MaterialLibBundle:Web:static-detail.html.twig', array(
                'material'   => $file,
                'thumbnails' => "",
                'editUrl'    => $this->generateUrl('material_edit', array('fileId' => $file['id']))
            ));
        } else {
            try {
                if ($file['type'] == 'video') {
                    $thumbnails = $this->getCloudFileService()->getDefaultHumbnails($file['globalId']);
                }
            } catch (\RuntimeException $e) {
                $thumbnails = array();
            }

            return $this->render('TopxiaAdminBundle:CloudFile:detail.html.twig', array(
                'material'   => $file,
                'thumbnails' => empty($thumbnails) ? "" : $thumbnails,
                'params'     => $request->query->all(),
                'editUrl'    => $this->generateUrl('material_edit', array('fileId' => $file['id']))
            ));
        }
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
        $user = $this->getCurrentUser();

        if (!$user->isTeacher() && !$user->isAdmin()) {
            throw $this->createAccessDeniedException('您无权访问此页面');
        }

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

        if (!$user->isTeacher() && !$user->isAdmin()) {
            return $this->createJsonResponse(false);
        }

        $data = $request->query->all();

        $collection = $this->getUploadFileService()->collectFile($user['id'], $data['fileId']);

        if (empty($collection)) {
            return $this->createJsonResponse(false);
        }

        return $this->createJsonResponse(true);
    }

    public function editAction(Request $request, $fileId)
    {
        $this->getUploadFileService()->tryManageFile($fileId);

        $fields = $request->request->all();

        $result = $this->getUploadFileService()->update($fileId, $fields);
        return $this->createJsonResponse($result);
    }

    public function downloadAction(Request $request, $fileId)
    {
        $this->getUploadFileService()->tryAccessFile($fileId);
        return $this->forward('TopxiaWebBundle:UploadFile:download', array(
            'request' => $request,
            'fileId'  => $fileId
        ));
    }

    public function deleteModalShowAction(Request $request)
    {
        $fileIds = $request->request->get('ids');

        $materials = $this->getCourseMaterialService()->findUsedCourseMaterials($fileIds, $courseId = 0);
        $files     = $this->getUploadFileService()->findFilesByIds($fileIds, 0);
        $files     = ArrayToolkit::index($files, 'id');

        return $this->render('MaterialLibBundle:Web:delete-file-modal.html.twig', array(
            'materials'     => $materials,
            'files'         => $files,
            'ids'           => $fileIds,
            'deleteFormUrl' => $this->generateUrl('material_batch_delete')
        ));
    }

    public function deleteAction(Request $request, $fileId)
    {
        $this->getUploadFileService()->tryManageFile($fileId);
        $result = $this->getMaterialLibService()->delete($fileId);
        return $this->createJsonResponse($result);
    }

    public function batchDeleteAction(Request $request)
    {
        $data = $request->request->all();

        if (isset($data['ids']) && $data['ids'] != "") {
            foreach ($data['ids'] as $fileId) {
                $this->getUploadFileService()->tryManageFile($fileId);
            }

            $this->getMaterialLibService()->batchDelete($data['ids']);
            return $this->createJsonResponse(true);
        }

        return $this->createJsonResponse(false);
    }

    public function batchShareAction(Request $request)
    {
        $data = $request->request->all();

        if (isset($data['ids']) && $data['ids'] != "") {
            foreach ($data['ids'] as $fileId) {
                $this->getUploadFileService()->tryManageFile($fileId);
            }

            $result = $this->getMaterialLibService()->batchShare($data['ids']);
            return $this->createJsonResponse($result);
        }

        return $this->createJsonResponse(false);
    }

    public function unshareAction(Request $request, $fileId)
    {
        $this->getUploadFileService()->tryManageFile($fileId);
        $result = $this->getMaterialLibService()->unShare($fileId);
        return $this->createJsonResponse($result);
    }

    public function batchTagShowAction(Request $request)
    {
        $data    = $request->request->all();
        $fileIds = preg_split('/,/', $data['fileIds']);

        $this->getMaterialLibService()->batchTagEdit($fileIds, $data['tags']);
        return $this->redirect($this->generateUrl('material_lib_browsing'));
    }

    public function generateThumbnailAction(Request $request, $globalId)
    {
        $this->getUploadFileService()->tryManageGlobalFile($globalId);

        $second = $request->query->get('second');

        return $this->createJsonResponse($this->getMaterialLibService()->getThumbnail($globalId, array('seconds' => $second)));
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getMaterialLibService()
    {
        return $this->getServiceKernel()->createService('MaterialLib:MaterialLib.MaterialLibService');
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
        return $this->getServiceKernel()->createService('File.UploadFileService');
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

    protected function getCloudFileService()
    {
        return $this->getServiceKernel()->createService('CloudFile.CloudFileService');
    }

    protected function getCourseMaterialService()
    {
        return $this->getServiceKernel()->createService('Course.MaterialService');
    }
}
