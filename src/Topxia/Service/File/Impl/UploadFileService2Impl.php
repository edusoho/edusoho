<?php

namespace Topxia\Service\File\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\BaseService;
use Topxia\Service\File\UploadFileService2;

class UploadFileService2Impl extends BaseService implements UploadFileService2
{
    static $implementor = array(
        'local' => 'File.LocalFileImplementor2',
        'cloud' => 'File.CloudFileImplementor2'
    );

    public function getFile($id)
    {
        $file = $this->getUploadFileDao()->getFile($id);

        if (empty($file)) {
            return null;
        }

        if (empty($file['globalId'])) {
            return $file;
        }

        return $this->getFileImplementor($file)->getFile($file);
    }

    /**
     *  此函数不走云
     *
     */
    public function getThinFile($id)
    {
        $file = $this->getUploadFileDao()->getFile($id);

        if (empty($file)) {
            return null;
        }

        return ArrayToolkit::parts($file, array('id', 'hashId', 'globalId', 'isPublic', 'targetId', 'targetType', 'filename', 'ext', 'fileSize', 'length', 'status', 'type', 'storage', 'createdUserId', 'createdTime'));
    }

    public function getFileByGlobalId($globalId)
    {
        $file = $this->getUploadFileDao()->getFileByGlobalId($globalId);

        if (empty($file)) {
            return null;
        }

        return $this->getFileImplementor($file)->getFile($file);
    }

    //TODO
    public function findCloudFilesByIds($fileIds)
    {
        $files = $this->getUploadFileDao()->findCloudFilesByIds($fileIds);

        if (empty($files)) {
            return array();
        }

        $cloudFiles = $this->getFileImplementor(array('storage' => 'cloud'))->findFiles($files, array());

        $cloudFiles = ArrayToolkit::index($cloudFiles, 'id');

        foreach ($files as $key => $file) {
            $files[$key] = $cloudFiles[$file['id']];
        }

        return $files;
    }

    /**
     * 不走云
     */
    public function findFilesByIds(array $ids)
    {
        $files = $this->getUploadFileDao()->findFilesByIds($ids);

        if (empty($files)) {
            return array();
        }

        return $files;
    }

    public function findFilesByTargetTypeAndTargetId($targetType, $targetId)
    {
        return $this->getUploadFileDao()->findFilesByTargetTypeAndTargetId($targetType, $targetId);
    }

    public function findFilesByTargetTypeAndTargetIds($targetType, $targetIds)
    {
        return $this->getUploadFileDao()->findFilesByTargetTypeAndTargetIds($targetType, $targetIds);
    }

    public function searchFiles($conditions, $orderBy, $start, $limit)
    {
        $filds = array();

        if (!empty($conditions['processStatus'])) {
            $filds['processStatus'] = $conditions['processStatus'];
        }

        $conditions = $this->_prepareSearchConditions($conditions);
        $files      = $this->getUploadFileDao()->searchFiles($conditions, $orderBy, $start, $limit);

        if (empty($files)) {
            return array();
        }

        $groupFiles = ArrayToolkit::group($files, 'storage');

        if (isset($groupFiles['cloud']) && !empty($groupFiles['cloud'])) {
            $cloudFiles = $this->getFileImplementor(array('storage' => 'cloud'))->findFiles($groupFiles['cloud'], $filds);

            $cloudFiles = ArrayToolkit::index($cloudFiles, 'id');

            foreach ($files as $key => $file) {
                if ($file['storage'] == 'cloud') {
                    $files[$key] = $cloudFiles[$file['id']];
                }
            }
        }

        return $files;
    }

    public function searchFilesCount($conditions)
    {
        $conditions = $this->_prepareSearchConditions($conditions);
        return $this->getUploadFileDao()->searchFileCount($conditions);
    }

    public function edit($fileId, $fields)
    {
        $file = $this->getUploadFileDao()->getFile($fileId);

        if ($file) {
            $this->updateTags($file, $fields);

            if (!empty($file['globalId'])) {
                $cloudFields = ArrayToolkit::parts($fields, array('name', 'tags', 'description', 'endShared', 'thumbNo', 'endUser'));

                if (!empty($cloudFields)) {
                    $this->getFileImplementor(array('storage' => 'cloud'))->updateFile($file['globalId'], $cloudFields);
                }
            }

            if (isset($fields['name'])) {
                $fields['filename'] = $fields['name'];
            }

            $fields = ArrayToolkit::parts($fields, array('isPublic', 'filename'));
            unset($fields['name']);

            if (!empty($fields)) {
                return $this->getUploadFileDao()->updateFile($file['id'], $fields);
            }
        }

        return false;
    }

    public function getDownloadFile($id)
    {
        $file = $this->getUploadFileDao()->getFile($id);

        if (empty($file)) {
            return array('error' => 'not_found', 'message' => '文件不存在，不能下载！');
        }

        return $this->getFileImplementor($file)->getDownloadFile($file);
    }

    public function getUploadAuth($params)
    {
        $user = $this->getCurrentUser();

        if (empty($user)) {
            throw $this->createServiceException("用户未登录，上传初始化失败！");
        }

        $setting           = $this->getSettingService()->get('storage');
        $params['storage'] = empty($setting['upload_mode']) ? 'local' : $setting['upload_mode'];

        $implementor = $this->getFileImplementorByStorage($params['storage']);

        $auth = $implementor->getUploadAuth($params);
        return $auth;
    }

    public function initUpload($params)
    {
        $user = $this->getCurrentUser();

        if (empty($user)) {
            throw $this->createServiceException("用户未登录，上传初始化失败！");
        }

        if (!ArrayToolkit::requireds($params, array('targetId', 'targetType', 'hash'))) {
            throw $this->createServiceException("参数缺失，上传初始化失败！");
        }

        $params['userId'] = $user['id'];
        $params           = ArrayToolkit::parts($params, array('id', 'directives', 'userId', 'targetId', 'targetType', 'bucket', 'hash', 'fileSize', 'fileName'));

        $setting           = $this->getSettingService()->get('storage');
        $params['storage'] = empty($setting['upload_mode']) ? 'local' : $setting['upload_mode'];
        $implementor       = $this->getFileImplementorByStorage($params['storage']);

        if (isset($params['id'])) {
            $file       = $this->getUploadFileDao()->getFile($params['id']);
            $initParams = $implementor->resumeUpload($file, $params);

            if ($initParams['resumed'] == 'ok' && $file) {
                $file = $this->getUploadFileDao()->updateFile($file['id'], array(
                    'filename'   => $params['fileName'],
                    'fileSize'   => $params['fileSize'],
                    'targetId'   => $params['targetId'],
                    'targetType' => $params['targetType']
                ));

                return $initParams;
            }
        }

        $preparedFile = $implementor->prepareUpload($params);

        if (!empty($preparedFile)) {
            $file       = $this->getUploadFileDao()->addFile($preparedFile);
            $params     = array_merge($params, $file);
            $initParams = $implementor->initUpload($params);
            $file       = $this->getUploadFileDao()->updateFile($file['id'], array('globalId' => $initParams['globalId']));
        } else {
            $initParams = $implementor->initUpload($params);
        }

        return $initParams;
    }

    public function finishedUpload($params)
    {
        $file              = $this->getUploadFileDao()->getFile($params['id']);
        $setting           = $this->getSettingService()->get('storage');
        $params['storage'] = empty($setting['upload_mode']) ? 'local' : $setting['upload_mode'];
        $implementor       = $this->getFileImplementorByStorage($params['storage']);

        if (empty($params['length'])) {
            $params['length'] = 0;
        }

        $finishParams = array(
            "length" => $params['length'],
            'name'   => $params['filename'],
            'size'   => $params['size']
        );

        $result = $implementor->finishedUpload($file, $params);

        if (empty($result) || !$result['success']) {
            throw $this->createServiceException("uploadFile失败，完成上传失败！");
        }

        $fields = array(
            'status'        => 'ok',
            'convertStatus' => $result['convertStatus'],
            'length'        => isset($result['length']) ? $result['length'] : 0,
            'fileName'      => $params['filename'],
            'fileSize'      => $params['size']
        );
        $file = $this->getUploadFileDao()->updateFile($file['id'], $fields);

        if ($file['targetType'] == 'headLeader') {
            $headLeaders = $this->getUploadFileDao()->getHeadLeaderFiles();

            foreach ($headLeaders as $headLeader) {
                if ($headLeader['id'] != $file['id']) {
                    $this->deleteFile($headLeader['id']);
                }
            }
        }
    }

    public function setFileProcessed($params)
    {
        try {
            $file = $this->getUploadFileDao()->getFileByGlobalId($params['globalId']);

            $fields = array(
                'convertStatus' => 'success'
            );

            $this->getUploadFileDao()->updateFile($file['id'], $fields);
        } catch (\Exception $e) {
            $msg = $e->getMessage();
        }
    }

    public function deleteFile($id)
    {
        $file   = $this->getUploadFileDao()->getFile($id);
        $result = $this->getFileImplementor($file)->deleteFile($file);

        if (isset($result['success']) && $result['success'] == true) {
            return $this->getUploadFileDao()->deleteFile($id);
        }

        return false;
    }

    public function deleteFiles(array $ids)
    {
        foreach ($ids as $id) {
            $this->getUploadFileDao()->deleteFile($id);
        }
    }

    public function deleteByGlobalId($globalId)
    {
        $result = $this->getUploadFileDao()->deleteByGlobalId($globalId);
        return $result;
    }

    public function increaseFileUsedCount($id)
    {
        $this->getUploadFileDao()->waveFileUsedCount($id, +1);
    }

    public function addShare($sourceUserId, $targetUserId)
    {
        $fileShareFields = array(
            'sourceUserId' => $sourceUserId,
            'targetUserId' => $targetUserId,
            'isActive'     => 1,
            'createdTime'  => time(),
            'updatedTime'  => time()
        );

        return $this->getUploadFileShareDao()->addShare($fileShareFields);
    }

    public function cancelShareFile($sourceUserId, $targetUserId)
    {
        $shareHistory = $this->getUploadFileShareDao()->findShareHistory($sourceUserId, $targetUserId);

        if (!empty($shareHistory)) {
            $fileShareFields = array(
                'isActive'    => 0,
                'updatedTime' => time()
            );

            $this->getUploadFileShareDao()->updateShare($shareHistory['id'], $fileShareFields);
        }
    }

    public function updateShare($shareHistoryId)
    {
        $fileShareFields = array(
            'isActive'    => 1,
            'updatedTime' => time()
        );

        return $this->getUploadFileShareDao()->updateShare($shareHistoryId, $fileShareFields);
    }

    public function decreaseFileUsedCount($id)
    {
        $this->getUploadFileDao()->waveFileUsedCount($id, -1);
    }

    public function findShareHistory($sourceUserId)
    {
        $shareHistories = $this->getUploadFileShareDao()->findShareHistoryByUserId($sourceUserId);

        return $shareHistories;
    }

    public function searchShareHistoryCount($conditions)
    {
        return $this->getUploadFileShareDao()->searchShareHistoryCount($conditions);
    }

    public function searchShareHistories($conditions, $orderBy, $start, $limit)
    {
        return $this->getUploadFileShareDao()->searchShareHistories($conditions, $orderBy, $start, $limit);
    }

    public function findActiveShareHistory($sourceUserId)
    {
        $shareHistories = $this->getUploadFileShareDao()->findActiveShareHistoryByUserId($sourceUserId);

        return $shareHistories;
    }

    public function findShareHistoryByUserId($sourceUserId, $targetUserId)
    {
        return $this->getUploadFileShareDao()->findShareHistory($sourceUserId, $targetUserId);
    }

    public function findMySharingContacts($targetUserId)
    {
        $userIds = $this->getUploadFileShareDao()->findSharesByTargetUserIdAndIsActive($targetUserId);

        if (!empty($userIds)) {
            return $this->getUserService()->findUsersByIds(ArrayToolkit::column($userIds, 'sourceUserId'));
        } else {
            return null;
        }
    }

    public function waveUploadFile($id, $field, $diff)
    {
        $this->getUploadFileDao()->waveUploadFile($id, $field, $diff);
    }

    public function reconvertFile($id, $convertCallback)
    {
        $file = $this->getFile($id);

        if (empty($file)) {
            throw $this->createServiceException('file not exist.');
        }

        $convertHash = $this->getFileImplementorByFile($file)->reconvertFile($file, $convertCallback);

        $this->setFileConverting($file['id'], $convertHash);

        return $convertHash;
    }

    public function collectFile($userId, $fileId)
    {
        if (empty($userId) || empty($fileId)) {
            throw $this->createServiceException("参数错误，请重新输入");
        }

        $collection = $this->getUploadFileCollectDao()->getCollectonByUserIdandFileId($userId, $fileId);

        if (empty($collection)) {
            $collection = array(
                'userId'      => $userId,
                'fileId'      => $fileId,
                'updatedTime' => time(),
                'createdTime' => time()
            );
            $collection = $this->getUploadFileCollectDao()->addCollection($collection);
            $result     = $this->getUploadFileDao()->getFile($collection['fileId']);
            return $result;
        }

        $this->getUploadFileCollectDao()->deleteCollection($collection['id']);
        return false;
    }

    public function findCollectionsByUserIdAndFileIds($fileIds, $userId)
    {
        if (empty($fileIds)) {
            return array();
        }

        $collections = $this->getUploadFileCollectDao()->findCollectonsByUserIdandFileIds($fileIds, $userId);
        return $collections;
    }

    public function findCollectionsByUserId($userId)
    {
        $collections = $this->getUploadFileCollectDao()->findCollectionsByUserId($userId);
        return $collections;
    }

    protected function updateTags($localFile, $fields)
    {
        if (!empty($fields['tags'])) {
            $tagNames = explode(',', $fields['tags']);
            $this->getUploadFileTagDao()->deleteByFileId($localFile['id']);

            foreach ($tagNames as $tagName) {
                $tag = $this->getTagService()->getTagByName($tagName);
                $this->getUploadFileTagDao()->add(array('tagId' => $tag['id'], 'fileId' => $localFile['id']));
            }
        }
    }

    protected function _prepareSearchConditions($conditions)
    {

        $conditions['createdUserIds'] = empty($conditions['createdUserIds']) ? array() : $conditions['createdUserIds'];

        if (isset($conditions['sourceFrom']) && ($conditions['sourceFrom'] == 'my') && !empty($conditions['currentUserId'])) {
            $conditions['createdUserIds'] = array($conditions['currentUserId']);
        }

        if (!empty($conditions['sourceFrom']) && $conditions['sourceFrom'] == 'sharing' && !empty($conditions['currentUserId'])) {
            $fromSharing = $this->getUploadFileShareDao()->findSharesByTargetUserIdAndIsActive($conditions['currentUserId'], 1);

            if (!empty($fromSharing)) {
                $item = array();

                foreach ($fromSharing as $key => $value) {
                    $item[$key] = $value['sourceUserId'];
                }

                $conditions['createdUserIds'] = $item;
            } else {
                $conditions['createdUserIds'] = array(0);
            }
        }

        if (!empty($conditions['sourceFrom']) && $conditions['sourceFrom'] == 'public') {
            $conditions['isPublic'] = 1;
            unset($conditions['createdUserId']);
            unset($conditions['createdUserIds']);
        }

        if (isset($conditions['sourceFrom']) && ($conditions['sourceFrom'] == 'favorite') && !empty($conditions['currentUserId'])) {
            $collections       = $this->findCollectionsByUserId($conditions['currentUserId']);
            $fileIds           = ArrayToolkit::column($collections, 'fileId');
            $conditions['ids'] = $fileIds ? $fileIds : array(0);
            unset($conditions['createdUserId']);
            unset($conditions['createdUserIds']);
        }

        if (empty($conditions['startDate'])) {
            unset($conditions['startDate']);
        }

        if (empty($conditions['endDate'])) {
            unset($conditions['endDate']);
        }

        if (isset($conditions['startDate'])) {
            $conditions['startDate'] = strtotime($conditions['startDate']);
        }

        if (isset($conditions['endDate'])) {
            $conditions['endDate'] = strtotime($conditions['endDate']);
        }

        if (isset($conditions['useStatus'])) {
            if ($conditions['useStatus'] == 'unused') {
                $conditions['endCount'] = 1;
            } elseif ($conditions['useStatus'] == 'used') {
                $conditions['startCount'] = 1;
            }
        } else {
            $conditions['startCount'] = 0;
        }

        if (!empty($conditions['tagId'])) {
            $files = $this->getUploadFileTagDao()->findByTagId($conditions['tagId']);
            $ids   = ArrayToolkit::column($files, 'fileId');

            if(isset($conditions['ids'])) {
              if ($ids) {
                  $conditions['ids'] = array_intersect($conditions['ids'],$ids);
                  if(empty($conditions['ids'])) {
                    $conditions['ids'] = array('-1');
                  }
              } else {
                  $conditions['ids'] = array('-1');
              }
            }
            if($conditions['sourceFrom'] == 'favorite' && !isset($conditions['ids'])){
              $conditions['ids'] = array('-1');
            }

            if($conditions['sourceFrom'] != 'favorite'){
              if ($ids) {
                  $conditions['ids'] = $ids;
              } else {
                  $conditions['ids'] = array('-1');
              }
            }
            unset($conditions['tagId']);
        }

        return $conditions;
    }

    protected function getUserService()
    {
        return $this->createService('User.UserService');
    }

    protected function getFileImplementorName($file)
    {
        return $file['storage'];
    }

    protected function getFileImplementor($file)
    {
        return $this->getFileImplementorByStorage($file['storage']);
    }

    protected function getFileImplementorByStorage($storage)
    {
        return $this->createFileImplementor($storage);
    }

    protected function createFileImplementor($key)
    {
        if (!array_key_exists($key, self::$implementor)) {
            throw $this->createServiceException(sprintf("`%s` File Implementor is not allowed.", $key));
        }

        return $this->createService(self::$implementor[$key]);
    }

    protected function getSettingService()
    {
        return $this->createService('System.SettingService');
    }

    protected function getTagService()
    {
        return $this->createService('Taxonomy.TagService');
    }

    protected function getUploadFileDao()
    {
        return $this->createDao('File.UploadFileDao');
    }

    protected function getUploadFileShareDao()
    {
        return $this->createDao('File.UploadFileShareDao');
    }

    protected function getUploadFileCollectDao()
    {
        return $this->createDao('File.UploadFileCollectDao');
    }

    protected function getUploadFileTagDao()
    {
        return $this->createDao('File.UploadFileTagDao');
    }
}

class FileFilter
{
    public static function filters($files)
    {
        $filterResult = array();

        if (empty($files)) {
            return $filterResult;
        }

        foreach ($files as $index => $file) {
            array_push($filterResult, array('id' => $file['id'], 'convertStatus' => $file['convertStatus']));
        }

        return $filterResult;
    }
}
