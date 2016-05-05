<?php

namespace Topxia\Service\File\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\BaseService;
use Topxia\Service\File\UploadFileService;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadFileServiceImpl extends BaseService implements UploadFileService
{
    static $implementor = array(
        'local' => 'File.LocalFileImplementor',
        'cloud' => 'File.CloudFileImplementor'
    );

    static $implementor2 = array(
        'local' => 'File.LocalFileImplementor2',
        'cloud' => 'File.CloudFileImplementor2'
    );

    public function getFile($id)
    {
        $file = $this->getUploadFileDao()->getFile($id);
        if (empty($file)) {
            return null;
        }

        return $this->getFileImplementorByFile($file)->getFile($file);
    }

    //合并getfile
    public function getFile2($id)
    {
        $file = $this->getUploadFileDao()->getFile($id);

        if (empty($file)) {
            return null;
        }

        if (empty($file['globalId'])) {
            return $file;
        }

        return $this->getFileImplementor2($file)->getFile($file);
    }

    //2
    public function getFileFromLeaf($id)
    {
        $file = $this->getUploadFileDao()->getFile($id);

        if (empty($file)) {
            return null;
        }

        if (empty($file['globalId'])) {
            return $file;
        }

        return $this->getFileImplementor2($file)->getFileFromLeaf($file);
    }

    //2
    public function getUploadFileInit($id)
    {
        return $this->getUploadFileInitDao()->getFile($id);
    }

    //合并getFile
    public function getThinFile($id)
    {
        $file = $this->getUploadFileDao()->getFile($id);

        if (empty($file)) {
            return null;
        }

        return ArrayToolkit::parts($file, array('id', 'hashId', 'globalId', 'isPublic', 'targetId', 'targetType', 'filename', 'ext', 'fileSize', 'length', 'status', 'type', 'storage', 'createdUserId', 'createdTime'));
    }

    //合并getFileByGlobalId
    public function getThinFileByGlobalId($globalId)
    {
        return $this->getUploadFileDao()->getFileByGlobalId($globalId);
    }

    //合并getFIleByGlobalId
    public function getFileByGlobalId2($globalId)
    {
        $file = $this->getUploadFileDao()->getFileByGlobalId($globalId);

        if (empty($file)) {
            return null;
        }

        return $this->getFileImplementor2($file)->getFile($file);
    }

    //2
    public function findCloudFilesByIds($fileIds)
    {
        $files = $this->getUploadFileDao()->findCloudFilesByIds($fileIds);

        if (empty($files)) {
            return array();
        }

        $cloudFiles = $this->getFileImplementor2(array('storage' => 'cloud'))->findFiles($files, array());

        $cloudFiles = ArrayToolkit::index($cloudFiles, 'id');

        foreach ($files as $key => $file) {
            $files[$key] = $cloudFiles[$file['id']];
        }

        return $files;
    }

    //合并findFilesByIds
    public function findThinFilesByIds(array $ids)
    {
        return $this->getUploadFileDao()->findFilesByIds($ids);
    }

    //合并findFileByIds
    public function findFilesByIds2(array $ids)
    {
        $files = $this->getUploadFileDao()->findFilesByIds($ids);

        if (empty($files)) {
            return array();
        }

        $groupFiles = ArrayToolkit::group($files, 'storage');

        if (isset($groupFiles['cloud']) && !empty($groupFiles['cloud'])) {
            $cloudFiles = $this->getFileImplementor2(array('storage' => 'cloud'))->findFiles($groupFiles['cloud'], array());

            $cloudFiles = ArrayToolkit::index($cloudFiles, 'id');

            foreach ($files as $key => $file) {
                if ($file['storage'] == 'cloud') {
                    $files[$key] = $cloudFiles[$file['id']];
                }
            }
        }

        return $files;
    }
    //2
    public function findFilesByTargetTypeAndTargetId($targetType, $targetId)
    {
        return $this->getUploadFileDao()->findFilesByTargetTypeAndTargetId($targetType, $targetId);
    }
    //2
    public function findFilesByTargetTypeAndTargetIds($targetType, $targetIds)
    {
        return $this->getUploadFileDao()->findFilesByTargetTypeAndTargetIds($targetType, $targetIds);
    }
    //合并serarchFiles
    public function searchFiles2($conditions, $orderBy, $start, $limit)
    {
        if (!empty($conditions['processStatus'])) {
            $cloudFileConditions['processStatus'] = $conditions['processStatus'];
            $conditions['storage']                = 'cloud';
            $conditions['existGlobalId']          = 0;
            $conditions                           = $this->_prepareSearchConditions($conditions);

            $files     = $this->getUploadFileDao()->searchFiles($conditions, $orderBy, 0, 99999);
            $globalIds = ArrayToolkit::column($files, 'globalId');

            if (empty($globalIds)) {
                return array();
            }

            $cloudFileConditions['nos'] = implode(',', $globalIds);

            $cloudFileConditions['start'] = $start;
            $cloudFileConditions['limit'] = $limit;

            $cloudFiles = $this->getFileImplementor2(array('storage' => 'cloud'))->search($cloudFileConditions);

            return $cloudFiles['data'];
        }

        $files = array();

        $conditions = $this->_prepareSearchConditions($conditions);

        $files = $this->getUploadFileDao()->searchFiles($conditions, $orderBy, $start, $limit);

        if (empty($files)) {
            return array();
        }

        $groupFiles = ArrayToolkit::group($files, 'storage');

        if (isset($groupFiles['cloud']) && !empty($groupFiles['cloud'])) {
            $cloudFileConditions        = array();
            $cloudFileConditions['nos'] = implode(',', ArrayToolkit::column($groupFiles['cloud'], 'globalId'));
            $cloudFiles                 = $this->getFileImplementor2(array('storage' => 'cloud'))->findFiles($groupFiles['cloud'], $cloudFileConditions);

            $cloudFiles = ArrayToolkit::index($cloudFiles, 'id');

            foreach ($files as $key => $file) {
                if ($file['storage'] == 'cloud') {
                    $files[$key] = $cloudFiles[$file['id']];
                }
            }
        }

        return $files;
    }
    //合并searchFilesCount
    public function searchFilesCount2($conditions)
    {
        if (!empty($conditions['processStatus'])) {
            $cloudFileConditions['processStatus'] = $conditions['processStatus'];
            $conditions['storage']                = 'cloud';
            $conditions['existGlobalId']          = 0;
            $conditions                           = $this->_prepareSearchConditions($conditions);
            $files                                = $this->getUploadFileDao()->searchFiles($conditions, array('createdTime', 'DESC'), 0, 99999);
            $globalIds                            = ArrayToolkit::column($files, 'globalId');

            if (empty($globalIds)) {
                return 0;
            }

            $cloudFileConditions['nos'] = implode(',', $globalIds);

            $cloudFiles = $this->getFileImplementor2(array('storage' => 'cloud'))->search($cloudFileConditions);

            return $cloudFiles['count'];
        }

        $conditions = $this->_prepareSearchConditions($conditions);
        $localCount = $this->getUploadFileDao()->searchFileCount($conditions);

        if (empty($localCount)) {
            return 0;
        }

        $files = $this->getUploadFileDao()->searchFiles($conditions, array('createdTime', 'DESC'), 0, 9999);

        $groupFiles = ArrayToolkit::group($files, 'storage');

        return $localCount;
    }
    //2
    public function edit($fileId, $fields)
    {
        $file = $this->getUploadFileDao()->getFile($fileId);

        if ($file) {
            $this->updateTags($file, $fields);

            if (!empty($file['globalId'])) {
                $cloudFields = ArrayToolkit::parts($fields, array('name', 'tags', 'description', 'thumbNo'));

                if (!empty($cloudFields)) {
                    $this->getFileImplementor2(array('storage' => 'cloud'))->updateFile($file['globalId'], $cloudFields);
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
    //2
    public function getDownloadFile($id)
    {
        $file = $this->getUploadFileDao()->getFile($id);

        if (empty($file)) {
            return array('error' => 'not_found', 'message' => '文件不存在，不能下载！');
        }

        return $this->getFileImplementor2($file)->getDownloadFile($file);
    }
    //2
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

    //2
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
        $params           = ArrayToolkit::parts($params, array(
            'id',
            'directives',
            'userId',
            'targetId',
            'targetType',
            'bucket',
            'hash',
            'fileSize',
            'fileName',
            'watermarks'
        ));

        $setting           = $this->getSettingService()->get('storage');
        $params['storage'] = empty($setting['upload_mode']) ? 'local' : $setting['upload_mode'];
        $implementor       = $this->getFileImplementorByStorage($params['storage']);

        if (isset($params['id'])) {
            $file       = $this->getUploadFileInitDao()->getFile($params['id']);
            $initParams = $implementor->resumeUpload($file, $params);

            if ($initParams['resumed'] == 'ok' && $file) {
                $file = $this->getUploadFileInitDao()->updateFile($file['id'], array(
                    'filename'   => $params['fileName'],
                    'fileSize'   => $params['fileSize'],
                    'targetId'   => $params['targetId'],
                    'targetType' => $params['targetType']
                ));

                return $initParams;
            }
        }

        $preparedFile = $implementor->prepareUpload($params);

        $file       = $this->getUploadFileInitDao()->addFile($preparedFile);
        $params     = array_merge($params, $file);
        $initParams = $implementor->initUpload($params);

        if ($params['storage'] == 'cloud') {
            $file = $this->getUploadFileInitDao()->updateFile($file['id'], array('globalId' => $initParams['globalId']));
        }

        return $initParams;
    }

    //2
    public function finishedUpload($params)
    {
        $connection = $this->getKernel()->getConnection();
        try {
            $connection->beginTransaction();

            $setting           = $this->getSettingService()->get('storage');
            $params['storage'] = empty($setting['upload_mode']) ? 'local' : $setting['upload_mode'];

            if (empty($params['length'])) {
                $params['length'] = 0;
            }

            $implementor = $this->getFileImplementorByStorage($params['storage']);

            $fields = array(
                'status'        => 'ok',
                'convertStatus' => 'none',
                'length'        => $params['length'],
                'fileSize'      => $params['size']
            );

            $file = $this->getUploadFileInitDao()->getFile($params['id']);
            $file = array_merge($file, $fields);
            $this->getUploadFileInitDao()->deleteFile($file['id']);

            $file = $this->getUploadFileDao()->addFile($file);

            $result = $implementor->finishedUpload($file, $params);

            if (empty($result) || !$result['success']) {
                throw $this->createServiceException("uploadFile失败，完成上传失败！");
            }

            $file = $this->getUploadFileDao()->updateFile($file['id'], array(
                'length' => isset($result['length']) ? $result['length'] : 0
            ));

            if ($file['targetType'] == 'headLeader') {
                $headLeaders = $this->getUploadFileDao()->getHeadLeaderFiles();

                foreach ($headLeaders as $headLeader) {
                    if ($headLeader['id'] != $file['id']) {
                        $this->deleteFile($headLeader['id']);
                    }
                }
            }

            $connection->commit();
            return $file;
        } catch (\Exception $e) {
            $connection->rollback();
            throw $e;
        }
    }

    //2
    public function moveFile($targetType, $targetId, $originalFile = null, $data)
    {
        return $this->getFileImplementorByStorage('local')->moveFile($targetType, $targetId, $originalFile, $data);
    }

    //2
    public function setFileProcessed($params)
    {
        try {
            $file = $this->getUploadFileInitDao()->getFileByGlobalId($params['globalId']);

            $fields = array(
                'convertStatus' => 'success'
            );

            $this->getUploadFileInitDao()->updateFile($file['id'], $fields);
        } catch (\Exception $e) {
            $msg = $e->getMessage();
        }
    }
    //合并deleteFile
    public function deleteFile2($id)
    {
        $file   = $this->getUploadFileDao()->getFile($id);
        $result = $this->getFileImplementor2($file)->deleteFile($file);

        if (isset($result['success']) && $result['success'] == true) {
            return $this->getUploadFileDao()->deleteFile($id);
        }

        return false;
    }

    //合并deleteFiles
    public function deleteFiles2(array $ids)
    {
        foreach ($ids as $id) {
            $this->getUploadFileDao()->deleteFile2($id);
        }
    }

    //2
    public function deleteByGlobalId($globalId)
    {
        $result = $this->getUploadFileDao()->deleteByGlobalId($globalId);
        return $result;
    }
    //2
    public function increaseFileUsedCount($id)
    {
        $this->getUploadFileDao()->waveFileUsedCount($id, +1);
    }

    //2
    public function decreaseFileUsedCount($id)
    {
        $this->getUploadFileDao()->waveFileUsedCount($id, -1);
    }

    //2
    public function searchShareHistoryCount($conditions)
    {
        return $this->getUploadFileShareDao()->searchShareHistoryCount($conditions);
    }

    //2
    public function searchShareHistories($conditions, $orderBy, $start, $limit)
    {
        return $this->getUploadFileShareDao()->searchShareHistories($conditions, $orderBy, $start, $limit);
    }

    //2
    public function findActiveShareHistory($sourceUserId)
    {
        $shareHistories = $this->getUploadFileShareDao()->findActiveShareHistoryByUserId($sourceUserId);

        return $shareHistories;
    }

    //合并findMySharingContacts
    public function findMySharingContacts2($targetUserId)
    {
        $userIds = $this->getUploadFileShareDao()->findSharesByTargetUserIdAndIsActive($targetUserId);

        if (!empty($userIds)) {
            return $this->getUserService()->findUsersByIds(ArrayToolkit::column($userIds, 'sourceUserId'));
        } else {
            return null;
        }
    }

    //合并reconvertFile
    public function reconvertFile2($id, $options = array())
    {
        $file = $this->getThinFile($id);

        if (empty($file)) {
            throw $this->createServiceException('file not exist.');
        }

        $convertHash = $this->getFileImplementorByStorage($file['storage'])->reconvert($file['globalId'], $options);

        return $convertHash;
    }

    //2
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

    //2
    public function findCollectionsByUserIdAndFileIds($fileIds, $userId)
    {
        if (empty($fileIds)) {
            return array();
        }

        $collections = $this->getUploadFileCollectDao()->findCollectonsByUserIdandFileIds($fileIds, $userId);
        return $collections;
    }
    //2
    public function findCollectionsByUserId($userId)
    {
        $collections = $this->getUploadFileCollectDao()->findCollectionsByUserId($userId);
        return $collections;
    }

    //合并getFileByTargetType
    public function getFileByTargetType2($targetType)
    {
        $file = $this->getUploadFileDao()->getFileByTargetType($targetType);

        if (empty($file)) {
            return null;
        }

        if (empty($file['globalId'])) {
            return $file;
        }

        return $this->getFileImplementor2($file)->getFile($file);
    }

    //2
    public function syncFile($file)
    {
        $this->getFileImplementorByStorage('cloud')->syncFile($file);
    }


    public function getFileByGlobalId($globalId)
    {
        $file = $this->getUploadFileDao()->getFileByGlobalId($globalId);

        if (empty($file)) {
            return null;
        }

        return $this->getFileImplementorByFile($file)->getFile($file);
    }

    public function getFileByHashId($hashId)
    {
        $file = $this->getUploadFileDao()->getFileByHashId($hashId);

        if (empty($file)) {
            return null;
        }

        return $this->getFileImplementorByFile($file)->getFile($file);
    }

    public function getFileByConvertHash($hash)
    {
        return $this->getUploadFileDao()->getFileByConvertHash($hash);
    }

    public function findFilesByIds(array $ids)
    {
        return $this->getUploadFileDao()->findFilesByIds($ids);
    }

    public function searchFiles($conditions, $sort, $start, $limit)
    {
        switch ($sort) {
            case 'latestUpdated':
                $orderBy = array('updatedTime', 'DESC');
                break;
            case 'oldestUpdated':
                $orderBy = array('updatedTime', 'ASC');
                break;
            case 'latestCreated':
                $orderBy = array('createdTime', 'DESC');
                break;
            case 'oldestCreated':
                $orderBy = array('createdTime', 'ASC');
                break;
            case 'extAsc':
                $orderBy = array('ext', 'ASC');
                break;
            case 'extDesc':
                $orderBy = array('ext', 'DESC');
                break;
            case 'nameAsc':
                $orderBy = array('filename', 'ASC');
                break;
            case 'nameDesc':
                $orderBy = array('filename', 'DESC');
                break;
            case 'sizeAsc':
                $orderBy = array('size', 'ASC');
                break;
            case 'sizeDesc':
                $orderBy = array('size',
                    'DESC'
                );
                break;
            default:
                throw $this->createServiceException('参数sort不正确。');
        }

        if (array_key_exists('source', $conditions) && $conditions['source'] == 'shared') {
            //Find all the users who is sharing with current user.
            $myFriends = $this->getUploadFileShareDao()->findSharesByTargetUserIdAndIsActive($conditions['currentUserId']);

            if (isset($myFriends)) {
                $createdUserIds = ArrayToolkit::column($myFriends, "sourceUserId");
            } else {
                //Browsing shared files, but nobody is sharing with current user.
                return array();
            }
        } elseif(array_key_exists('source', $conditions) && $conditions['source'] == 'public') {
          $conditions['isPublic'] = 1 ;
        } elseif(array_key_exists('source', $conditions) && $conditions['source'] == 'collection') {
          $collections = $this->getUploadFileCollectDao()->findCollectionsByUserId($conditions['currentUserId']);
          if(!empty($collections)) {
            $fileIds = ArrayToolkit::column($collections,"fileId");
            $conditions['ids'] = $fileIds;
          } else {
            return array();
          }
        } elseif (isset($conditions['currentUserId'])) {
            $createdUserIds = array($conditions['currentUserId']);
        }

        if (isset($createdUserIds)) {
            $conditions['createdUserIds'] = $createdUserIds;
        }

        $files = $this->getUploadFileDao()->searchFiles($conditions, $orderBy, $start, $limit);
        return $files;
    }

    public function searchFileCount($conditions)
    {
        if (array_key_exists('source', $conditions) && $conditions['source'] == 'shared') {
            //Find all the users who is sharing with current user.
            $myFriends = $this->getUploadFileShareDao()->findShareHistoryByUserId($conditions['currentUserId']);

            if (isset($myFriends)) {
                $createdUserIds = ArrayToolkit::column($myFriends, "sourceUserId");
            } else {
                //Browsing shared files, but nobody is sharing with current user.
                return 0;
            }
        } elseif(array_key_exists('source', $conditions) && $conditions['source'] == 'public') {
          $conditions['isPublic'] = 1 ;
        } elseif(array_key_exists('source', $conditions) && $conditions['source'] == 'collection') {
          $collections = $this->getUploadFileCollectDao()->findCollectionsByUserId($conditions['currentUserId']);
          if(!empty($collections)) {
            $fileIds = ArrayToolkit::column($collections,"fileId");
            $conditions['ids'] = $fileIds;
          } else {
            return array();
          }
        } elseif (isset($conditions['currentUserId'])) {
            $createdUserIds = array($conditions['currentUserId']);
        }

        if (isset($createdUserIds)) {
            $conditions['createdUserIds'] = $createdUserIds;
        }

        return $this->getUploadFileDao()->searchFileCount($conditions);
    }

    public function addFile($targetType, $targetId, array $fileInfo = array(), $implemtor = 'local', UploadedFile $originalFile = null)
    {
        $file = $this->getFileImplementor($implemtor)->addFile($targetType, $targetId, $fileInfo, $originalFile);

        $file = $this->getUploadFileDao()->addFile($file);

        return $file;
    }

    public function renameFile($id, $newFilename)
    {
        $this->getUploadFileDao()->updateFile($id, array('filename' => $newFilename));
        return $this->getFile($id);
    }

    public function deleteFile($id)
    {
        $file = $this->getFile($id);

        if (empty($file)) {
            throw $this->createServiceException("文件(#{$id})不存在，删除失败");
        }

        $deleted = $this->getFileImplementorByFile($file)->deleteFile($file);

        if ($deleted) {
            $deleted = $this->getUploadFileDao()->deleteFile($id);
        }

        return $deleted;
    }

    public function deleteFiles(array $ids)
    {
        foreach ($ids as $id) {
            $this->deleteFile($id);
        }
    }

    public function saveConvertResult($id, array $result = array())
    {
        $file = $this->getFile($id);

        if (empty($file)) {
            throw $this->createServiceException("文件(#{$id})不存在，转换失败");
        }

        $file = $this->getFileImplementorByFile($file)->saveConvertResult($file, $result);

        $this->getUploadFileDao()->updateFile($id, array(
            'convertStatus' => $file['convertStatus'],
            'metas2'        => json_encode($file['metas2']),
            'updatedTime'   => time()
        ));

        return $this->getFile($id);
    }

    public function saveConvertResult3($id, array $result = array())
    {
        $file = $this->getFile($id);

        if (empty($file)) {
            throw $this->createServiceException("文件(#{$id})不存在，转换失败");
        }

        $file['convertParams']['convertor'] = 'HLSEncryptedVideo';

        $fileNeedUpdateFields = array();

        $file = $this->getFileImplementorByFile($file)->saveConvertResult($file, $result);

        if ($file['convertStatus'] == 'success') {
            $fileNeedUpdateFields['convertParams'] = json_encode($file['convertParams']);
            $fileNeedUpdateFields['metas2']        = json_encode($file['metas2']);
            $fileNeedUpdateFields['updatedTime']   = time();
            $this->getUploadFileDao()->updateFile($id, $fileNeedUpdateFields);
        }

        return $this->getFile($id);
    }

    public function convertFile($id, $status, array $result = array(), $callback = null)
    {
        $statuses = array('none', 'waiting', 'doing', 'success', 'error');

        if (!in_array($status, $statuses)) {
            throw $this->createServiceException('状态不正确，变更文件转换状态失败！');
        }

        $file = $this->getFile($id);

        if (empty($file)) {
            throw $this->createServiceException("文件(#{$id})不存在，转换失败");
        }

        $file = $this->getFileImplementorByFile($file)->convertFile($file, $status, $result, $callback);

        $this->getUploadFileDao()->updateFile($id, array(
            'convertStatus' => $file['convertStatus'],
            'metas2'        => $file['metas2'],
            'updatedTime'   => time()
        ));

        return $this->getFile($id);
    }

    public function setFileConverting($id, $convertHash)
    {
        $file = $this->getFile($id);

        if (empty($file)) {
            throw $this->createServiceException('file not exist.');
        }

        // $status = $file['convertStatus'] == 'success' ? 'success' : 'waiting';

        $fields = array(
            'convertStatus' => 'waiting',
            'convertHash'   => $convertHash,
            'updatedTime'   => time()
        );
        $this->getUploadFileDao()->updateFile($id, $fields);

        return $this->getFile($id);
    }

    public function makeUploadParams($params)
    {
        return $this->getFileImplementor($params['storage'])->makeUploadParams($params);
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

    public function reconvertOldFile($id, $convertCallback, $pipeline)
    {
        $result = array();

        $file = $this->getFile($id);

        if (empty($file)) {
            return array('error' => 'file_not_found', 'message' => "文件(#{$id})不存在");
        }

        if ($file['storage'] != 'cloud') {
            return array('error' => 'not_cloud_file', 'message' => "文件(#{$id})，不是云文件。");
        }

        if ($file['type'] != 'video') {
            return array('error' => 'not_video_file', 'message' => "文件(#{$id})，不是视频文件。");
        }

        if ($file['targetType'] != 'courselesson') {
            return array('error' => 'not_course_file', 'message' => "文件(#{$id})，不是课时文件。");
        }

        $target = $this->createService('Course.CourseService')->getCourse($file['targetId']);

        if (empty($target)) {
            return array('error' => 'course_not_exist', 'message' => "文件(#{$id})所属的课程已删除。");
        }

        if (!empty($file['convertParams']['convertor']) && $file['convertParams']['convertor'] == 'HLSEncryptedVideo') {
            return array('error' => 'already_converted', 'message' => "文件(#{$id})已转换");
        }

        $fileNeedUpdateFields = array();

        if (!empty($file['convertParams']['convertor']) && $file['convertParams']['convertor'] == 'HLSVideo') {
            $file['convertParams']['hlsKeyUrl'] = 'http://hlskey.edusoho.net/placeholder';
            $file['convertParams']['hlsKey']    = $this->generateKey(16);

            if ($file['convertParams']['videoQuality'] == 'low') {
                $file['convertParams']['videoQuality'] = 'normal';
                $file['convertParams']['video']        = array('440k', '640k', '1000K');
            }

            $fileNeedUpdateFields['convertParams'] = json_encode($file['convertParams']);
            $file['convertParams']['convertor']    = 'HLSEncryptedVideo';
        }

        if (empty($file['convertParams'])) {
            $convertParams = array(
                'convertor'    => 'HLSEncryptedVideo',
                'segtime'      => 10,
                'videoQuality' => 'normal',
                'audioQuality' => 'normal',
                'video'        => array('440k', '640k', '1000K'),
                'audio'        => array('48k', '64k', '96k'),
                'hlsKeyUrl'    => 'http://hlskey.edusoho.net/placeholder',
                'hlsKey'       => $this->generateKey(16)
            );

            $file['convertParams'] = $convertParams;

            $convertParams['convertor']            = 'HLSVideo';
            $fileNeedUpdateFields['convertParams'] = json_encode($convertParams);
        }

        $convertHash = $this->getFileImplementorByFile($file)->reconvertOldFile($file, $convertCallback, $pipeline);

        if (empty($convertHash)) {
            return array('error' => 'convert_request_failed', 'message' => "文件(#{$id})转换请求失败！");
        }

        $fileNeedUpdateFields['convertHash'] = $convertHash;
        $fileNeedUpdateFields['updatedTime'] = time();

        $this->getUploadFileDao()->updateFile($file['id'], $fileNeedUpdateFields);

        $subTarget = $this->createService('Course.CourseService')->findLessonsByTypeAndMediaId('video', $file['id']) ?: array();

        if (!empty($subTarget)) {
            $subTarget = $subTarget[0];
        }

        return array(
            'convertHash' => $convertHash,
            'courseId'    => empty($subTarget['courseId']) ? $target['targetId'] : $subTarget['courseId'],
            'lessonId'    => empty($subTarget['id']) ? 0 : $subTarget['id']
        );
    }

    public function getMediaInfo($key, $type)
    {
        return $this->getFileImplementor('cloud')->getMediaInfo($key, $type);
    }

    public function getFileByTargetType($targetType)
    {
        return $this->getUploadFileDao()->getFileByTargetType($targetType);
    }

    public function findMySharingContacts($targetUserId)
    {
        $userIds = $this->getUploadFileShareDao()->findMySharingContacts($targetUserId);

        if (!empty($userIds)) {
            return $this->getUserService()->findUsersByIds(ArrayToolkit::column($userIds, 'sourceUserId'));
        } else {
            return null;
        }
    }

    public function findShareHistory($sourceUserId)
    {
        $shareHistories = $this->getUploadFileShareDao()->findShareHistoryByUserId($sourceUserId);

        return $shareHistories;
    }

    public function shareFiles($sourceUserId, $targetUserIds)
    {
        foreach ($targetUserIds as $targetUserId) {
            //Ignore sharing request if the sourceUserId equasls to targetUserId

            if ($targetUserId != $sourceUserId) {
                $shareHistory = $this->getUploadFileShareDao()->findShareHistory($sourceUserId, $targetUserId);

                if (isset($shareHistory)) {
                    //File sharing record exists, update the existing record
                    $fileShareFields = array(
                        'isActive'    => 1,
                        'updatedTime' => time()
                    );

                    $this->getUploadFileShareDao()->updateShare($shareHistory['id'], $fileShareFields);
                } else {
                    //Add new file sharing record
                    $fileShareFields = array(
                        'sourceUserId' => $sourceUserId,
                        'targetUserId' => $targetUserId,
                        'isActive'     => 1,
                        'createdTime'  => time(),
                        'updatedTime'  => time()
                    );

                    $this->getUploadFileShareDao()->addShare($fileShareFields);
                }
            }
        }
    }

    public function findShareHistoryByUserId($sourceUserId, $targetUserId)
    {
        return $this->getUploadFileShareDao()->findShareHistory($sourceUserId, $targetUserId);
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

    public function updateShare($shareHistoryId)
    {
        $fileShareFields = array(
            'isActive'    => 1,
            'updatedTime' => time()
        );

        return $this->getUploadFileShareDao()->updateShare($shareHistoryId, $fileShareFields);
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

    public function waveUploadFile($id, $field, $diff)
    {
        $this->getUploadFileDao()->waveUploadFile($id, $field, $diff);
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
            }

            if ($conditions['useStatus'] == 'used') {
                $conditions['startCount'] = 1;
            }
        }

        if (!empty($conditions['tagId'])) {
            $files = $this->getUploadFileTagDao()->findByTagId($conditions['tagId']);
            $ids   = ArrayToolkit::column($files, 'fileId');

            if (isset($conditions['ids'])) {
                if ($ids) {
                    $conditions['ids'] = array_intersect($conditions['ids'], $ids);

                    if (empty($conditions['ids'])) {
                        $conditions['ids'] = array('-1');
                    }
                } else {
                    $conditions['ids'] = array('-1');
                }
            }

            if ($conditions['sourceFrom'] == 'favorite' && !isset($conditions['ids'])) {
                $conditions['ids'] = array('-1');
            }

            if ($conditions['sourceFrom'] != 'favorite') {
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

    protected function generateKey($length = 0)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

        $key = '';

        for ($i = 0; $i < 16; $i++) {
            $key .= $chars[mt_rand(0, strlen($chars) - 1)];
        }

        return $key;
    }

    protected function getFileImplementorByFile($file)
    {
        return $this->getFileImplementor($file['storage']);
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

    protected function getUserService()
    {
        return $this->createService('User.UserService');
    }

    protected function getFileImplementor($key)
    {
        if (!array_key_exists($key, self::$implementor)) {
            throw $this->createServiceException(sprintf("`%s` File Implementor is not allowed.", $key));
        }

        return $this->createService(self::$implementor[$key]);
    }

    protected function getLogService()
    {
        return $this->createService('System.LogService');
    }

    protected function getFileImplementor2($file)
    {
        return $this->getFileImplementorByStorage($file['storage']);
    }

    protected function getFileImplementorByStorage($storage)
    {
        return $this->createFileImplementor($storage);
    }

    protected function createFileImplementor($key)
    {
        if (!array_key_exists($key, self::$implementor2)) {
            throw $this->createServiceException(sprintf("`%s` File Implementor is not allowed.", $key));
        }

        return $this->createService(self::$implementor2[$key]);
    }

    protected function getSettingService()
    {
        return $this->createService('System.SettingService');
    }

    protected function getTagService()
    {
        return $this->createService('Taxonomy.TagService');
    }

    protected function getUploadFileTagDao()
    {
        return $this->createDao('File.UploadFileTagDao');
    }

    protected function getUploadFileInitDao()
    {
        return $this->createDao('File.UploadFileInitDao');
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
