<?php

namespace Topxia\Service\File\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\BaseService;
use Topxia\Service\File\UploadFileService;
use Topxia\Service\File\FireWall\FireWallFactory;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadFileServiceImpl extends BaseService implements UploadFileService
{
    static $implementor = array(
        'local' => 'File.LocalFileImplementor',
        'cloud' => 'File.CloudFileImplementor'
    );

    public function getFile($id)
    {
        $file = $this->getUploadFileDao()->getFile($id);

        if (empty($file)) {
            return null;
        }

        return $this->getFileImplementor($file['storage'])->getFile($file);
    }

    public function getFullFile($id)
    {
        $file = $this->getUploadFileDao()->getFile($id);

        if (empty($file)) {
            return null;
        }

        if (empty($file['globalId'])) {
            return $file;
        }

        return $this->getFileImplementor($file['storage'])->getFullFile($file);
    }

    public function getUploadFileInit($id)
    {
        return $this->getUploadFileInitDao()->getFile($id);
    }

    public function getFileByGlobalId($globalId)
    {
        $file = $this->getUploadFileDao()->getFileByGlobalId($globalId);

        if (empty($file)) {
            return null;
        }

        return $this->getFileImplementor($file['storage'])->getFullFile($file);
    }

    public function findFilesByIds(array $ids, $showCloud = 0)
    {
        $files = $this->getUploadFileDao()->findFilesByIds($ids);
        if (empty($files)) {
            return array();
        }

        if ($showCloud) {
            $files = $this->getFileImplementor('cloud')->findFiles($files, array());
        }

        return $files;
    }

    public function findFilesByTargetTypeAndTargetIds($targetType, $targetIds)
    {
        return $this->getUploadFileDao()->findFilesByTargetTypeAndTargetIds($targetType, $targetIds);
    }

    public function update($fileId, $fields)
    {
        $file = $this->getUploadFileDao()->getFile($fileId);

        if ($file) {
            $this->updateTags($file, $fields);

            if (!empty($file['globalId'])) {
                $cloudFields = ArrayToolkit::parts($fields, array('name', 'tags', 'description', 'thumbNo'));

                if (!empty($cloudFields)) {
                    $this->getFileImplementor('cloud')->updateFile($file['globalId'], $cloudFields);
                }
            }

            if (isset($fields['name'])) {
                $fields['filename'] = $fields['name'];
                unset($fields['name']);
            }

            $fields = ArrayToolkit::parts($fields, array('isPublic', 'filename', 'description', 'targetId', 'useType', 'usedCount'));

            if (!empty($fields)) {
                return $this->getUploadFileDao()->updateFile($file['id'], $fields);
            }
        }

        return false;
    }

    public function getDownloadMetas($id)
    {
        $file = $this->getUploadFileDao()->getFile($id);

        if (empty($file)) {
            return array('error' => 'not_found', 'message' => '文件不存在，不能下载！');
        }

        return $this->getFileImplementor($file['storage'])->getDownloadFile($file);
    }

    public function getUploadAuth($params)
    {
        $user = $this->getCurrentUser();

        if (empty($user)) {
            throw $this->createServiceException("用户未登录，上传初始化失败！");
        }

        $setting           = $this->getSettingService()->get('storage');
        $params['storage'] = empty($setting['upload_mode']) ? 'local' : $setting['upload_mode'];

        $implementor = $this->getFileImplementor($params['storage']);

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
        $implementor       = $this->getFileImplementor($params['storage']);

        if (isset($params['id'])) {
            $file       = $this->getUploadFileInitDao()->getFile($params['id']);
            $initParams = $implementor->resumeUpload($file, $params);

            if ($initParams['resumed'] == 'ok' && $file && $file['status'] != 'ok') {
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
        $file         = $this->getUploadFileInitDao()->addFile($preparedFile);
        $params       = array_merge($params, $file);
        $initParams   = $implementor->initUpload($params);

        if ($params['storage'] == 'cloud') {
            $file = $this->getUploadFileInitDao()->updateFile($file['id'], array('globalId' => $initParams['globalId']));
        }

        $this->getLogger('UploadFileService')->info("initUpload 上传文件： #{$file['id']}");

        return $initParams;
    }

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

            $implementor = $this->getFileImplementor($params['storage']);

            $fields = array(
                'status'        => 'ok',
                'convertStatus' => 'none',
                'length'        => $params['length'],
                'fileSize'      => $params['size']
            );

            $file = $this->getUploadFileInitDao()->updateFile($params['id'], array('status' => 'ok'));

            $file   = array_merge($file, $fields);
            $file   = $this->getUploadFileDao()->addFile($file);
            $result = $implementor->finishedUpload($file, $params);

            if (empty($result) || !$result['success']) {
                throw $this->createServiceException("uploadFile失败，完成上传失败！");
            }

            $file = $this->getUploadFileDao()->updateFile($file['id'], array(
                'length' => isset($result['length']) ? $result['length'] : 0
            ));

            $this->getLogService()->info('upload_file', 'create', "新增文件(#{$file['id']})", $file);

            $this->getLogger('UploadFileService')->info("finishedUpload 添加文件：#{$file['id']}");

            if ($file['targetType'] == 'headLeader') {
                $headLeaders = $this->getUploadFileDao()->getHeadLeaderFiles();

                foreach ($headLeaders as $headLeader) {
                    if ($headLeader['id'] != $file['id']) {
                        $this->deleteFile($headLeader['id']);
                    }
                }
            }

            $this->dispatchEvent("upload.file.finish", array('file' => $file));

            $connection->commit();
            return $file;
        } catch (\Exception $e) {
            $connection->rollback();
            throw $e;
        }
    }

    public function moveFile($targetType, $targetId, $originalFile = null, $data)
    {
        return $this->getFileImplementor('local')->moveFile($targetType, $targetId, $originalFile, $data);
    }

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

    public function deleteByGlobalId($globalId)
    {
        $file = $this->getUploadFileDao()->getFileByGlobalId($globalId);

        if (empty($file)) {
            return null;
        }

        $result = $this->getUploadFileDao()->deleteByGlobalId($globalId);

        $this->getLogService()->info('upload_file', 'delete', "删除文件globalId (#{$globalId})", $file);

        return $result;
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

    public function reconvertFile($id, $options = array())
    {
        $file = $this->getFile($id);

        if (empty($file)) {
            throw $this->createServiceException('file not exist.');
        }

        $convertHash = $this->getFileImplementor($file['storage'])->reconvert($file['globalId'], $options);

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

        $convertHash = $this->getFileImplementor($file['storage'])->reconvertOldFile($file, $convertCallback, $pipeline);

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

    public function syncFile($file)
    {
        $this->getFileImplementor('cloud')->syncFile($file);
    }

    public function getFileByHashId($hashId)
    {
        $file = $this->getUploadFileDao()->getFileByHashId($hashId);

        if (empty($file)) {
            return null;
        }

        return $this->getFileImplementor($file['storage'])->getFile($file);
    }

    public function getFileByConvertHash($hash)
    {
        return $this->getUploadFileDao()->getFileByConvertHash($hash);
    }

    public function searchFiles($conditions, $orderBy, $start, $limit)
    {
        $conditions = $this->_prepareSearchConditions($conditions);

        if ($this->hasProcessStatusCondition($conditions)) {
            return $this->searchFilesFromCloud($conditions, $orderBy, $start, $limit);
        } else {
            return $this->searchFilesFromLocal($conditions, $orderBy, $start, $limit);
        }
    }

    protected function searchFilesFromCloud($conditions, $orderBy, $start, $limit)
    {
        $files     = $this->getUploadFileDao()->searchFiles($conditions, $orderBy, 0, PHP_INT_MAX);
        $globalIds = ArrayToolkit::column($files, 'globalId');

        if (empty($globalIds)) {
            return array();
        }

        $cloudFileConditions = array(
            'processStatus' => $conditions['processStatus'],
            'nos'           => implode(',', $globalIds),
            'start'         => $start,
            'limit'         => $limit
        );
        if (isset($conditions['resType'])) {
            $cloudFileConditions['resType'] = $conditions['resType'];
        }

        $cloudFiles = $this->getFileImplementor('cloud')->search($cloudFileConditions);

        return $cloudFiles['data'];
    }

    protected function searchFilesFromLocal($conditions, $orderBy, $start, $limit)
    {
        $files = $this->getUploadFileDao()->searchFiles($conditions, $orderBy, $start, $limit);

        if (empty($files)) {
            return array();
        }

        $groupFiles = ArrayToolkit::group($files, 'storage');

        if (isset($groupFiles['cloud']) && !empty($groupFiles['cloud'])) {
            $cloudFileConditions = array(
                'nos' => implode(',', ArrayToolkit::column($groupFiles['cloud'], 'globalId'))
            );
            if (isset($conditions['resType'])) {
                $cloudFileConditions['resType'] = $conditions['resType'];
            }
            $cloudFiles = $this->getFileImplementor('cloud')->findFiles($groupFiles['cloud'], $cloudFileConditions);
            $cloudFiles = ArrayToolkit::index($cloudFiles, 'id');

            foreach ($files as $key => $file) {
                if ($file['storage'] == 'cloud') {
                    $files[$key] = $cloudFiles[$file['id']];
                }
            }
        }

        return $files;
    }

    protected function hasProcessStatusCondition($conditions)
    {
        return !empty($conditions['processStatus']);
    }

    public function searchFileCount($conditions)
    {
        $conditions = $this->_prepareSearchConditions($conditions);

        if ($this->hasProcessStatusCondition($conditions)) {
            return $this->searchFileCountFromCloud($conditions);
        } else {
            return $this->getUploadFileDao()->searchFileCount($conditions);
        }
    }

    public function searchFileCountFromCloud($conditions)
    {
        $files     = $this->getUploadFileDao()->searchFiles($conditions, array('createdTime', 'DESC'), 0, PHP_INT_MAX);
        $globalIds = ArrayToolkit::column($files, 'globalId');

        if (empty($globalIds)) {
            return 0;
        }

        $cloudFileConditions = array(
            'processStatus' => $conditions['processStatus']
        );
        $globalArray = array_chunk($globalIds, 20);
        $count       = 0;

        foreach ($globalArray as $key => $globals) {
            $cloudFileConditions['nos'] = implode(',', $globals);

            $cloudFiles = $this->getFileImplementor('cloud')->search($cloudFileConditions);
            $count += $cloudFiles['count'];
        }

        return $count;
    }

    public function addFile($targetType, $targetId, array $fileInfo = array(), $implemtor = 'local', UploadedFile $originalFile = null)
    {
        $file = $this->getFileImplementor($implemtor)->addFile($targetType, $targetId, $fileInfo, $originalFile);

        $file = $this->getUploadFileDao()->addFile($file);

        $this->getLogService()->info('upload_file', 'create', "添加文件(#{$file['id']})", $file);
        $this->getLogger('UploadFileService')->info("addFile 添加文件：#{$file['id']}");

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
            return false;
        }

        $result = $this->getFileImplementor($file['storage'])->deleteFile($file);

        if (isset($result['success']) && $result['success']) {
            $result = $this->getUploadFileDao()->deleteFile($id);
        }

        $this->dispatchEvent("upload.file.delete", $file);
        $this->getLogService()->info('upload_file', 'delete', "删除文件(#{$id})", $file);

        return $result;
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

        $file = $this->getFileImplementor($file['storage'])->saveConvertResult($file, $result);

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

        $file = $this->getFileImplementor($file['storage'])->saveConvertResult($file, $result);

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

        $file = $this->getFileImplementor($file['storage'])->convertFile($file, $status, $result, $callback);

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

    public function getMediaInfo($key, $type)
    {
        return $this->getFileImplementor('cloud')->getMediaInfo($key, $type);
    }

    public function getFileByTargetType($targetType)
    {
        $file = $this->getUploadFileDao()->getFileByTargetType($targetType);

        if (empty($file)) {
            return null;
        }

        return $this->getFileImplementor($file['storage'])->getFullFile($file);
    }

    public function tryManageFile($fileId)
    {
        $user = $this->getCurrentUser();

        if (!$user->isTeacher()) {
            throw $this->createAccessDeniedException('您无权访问此文件！');
        }

        $file = $this->getFullFile($fileId);

        if (empty($file)) {
            throw $this->createNotFoundException();
        }

        if ($user->isAdmin()) {
            return $file;
        }

        if (!$user->isAdmin() && $user["id"] != $file["createdUserId"]) {
            throw $this->createAccessDeniedException('您无权访问此页面');
        }

        return $file;
    }

    public function tryManageGlobalFile($globalFileId)
    {
        $user = $this->getCurrentUser();

        if (!$user->isTeacher()) {
            throw $this->createAccessDeniedException('您无权访问此文件！');
        }

        $file = $this->getFileByGlobalId($globalFileId);

        if (empty($file)) {
            throw $this->createNotFoundException();
        }

        if ($user->isAdmin()) {
            return $file;
        }

        if (!$user->isAdmin() && $user["id"] != $file["createdUserId"]) {
            throw $this->createAccessDeniedException('您无权访问此页面');
        }

        return $file;
    }

    // TODO
    public function tryAccessFile($fileId)
    {
        $file = $this->getFullFile($fileId);

        if (empty($file)) {
            throw $this->createNotFoundException();
        }

        $user = $this->getCurrentUser();

        if ($user->isAdmin()) {
            return $file;
        }

        if ($file['isPublic'] == 1) {
            return $file;
        }

        if ($file['createdUserId'] == $user['id']) {
            return $file;
        }

        $shares = $this->findShareHistory($file['createdUserId']);

        foreach ($shares as $share) {
            if ($share['targetUserId'] == $user['id']) {
                return $file;
            }
        }

        throw $this->createAccessDeniedException('您无权访问此文件！');
    }

    public function canManageFile($fileId)
    {
        $user = $this->getCurrentUser();
        $file = $this->getFullFile($fileId);

        if (!$user->isTeacher()) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if (!$user->isAdmin() && $user['id'] != $file['createdUserId']) {
            return false;
        }

        return true;
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

    public function findShareHistory($sourceUserId)
    {
        return $this->getUploadFileShareDao()->findShareHistoryByUserId($sourceUserId);
    }

    public function shareFiles($sourceUserId, $targetUserIds)
    {
        foreach ($targetUserIds as $targetUserId) {
            if ($targetUserId != $sourceUserId) {
                $shareHistory = $this->getUploadFileShareDao()->findShareHistory($sourceUserId, $targetUserId);

                if (isset($shareHistory)) {
                    $this->updateShare($shareHistory['id']);
                } else {
                    $this->addShare($sourceUserId, $targetUserId);
                }
            }
        }
        return true;
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
        return $this->getUploadFileDao()->waveUploadFile($id, $field, $diff);
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
        if ($this->hasProcessStatusCondition($conditions)) {
            $conditions['storage']       = 'cloud';
            $conditions['existGlobalId'] = 0;
        }

        if (isset($conditions['source']) && !empty($conditions['source'])) {
            if ($conditions['source'] == 'upload') {
                $conditions['createdUserId'] = $conditions['currentUserId'];
            } elseif ($conditions['source'] == 'shared') {
                $sharedToMe = $this->getUploadFileShareDao()->findSharesByTargetUserIdAndIsActive($conditions['currentUserId']);

                if ($sharedToMe) {
                    $conditions['createdUserIds'] = ArrayToolkit::column($sharedToMe, "sourceUserId");
                } else {
                    $conditions['createdUserIds'] = array();
                }
            } elseif ($conditions['source'] == 'public') {
                $conditions['isPublic'] = 1;
            } elseif ($conditions['source'] == 'collection') {
                $collections = $this->getUploadFileCollectDao()->findCollectionsByUserId($conditions['currentUserId']);

                if (!empty($collections)) {
                    $conditions['ids'] = ArrayToolkit::column($collections, 'fileId');
                } else {
                    $conditions['ids'] = array();
                }
            }
        }

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

        if (isset($conditions['startDate']) && !empty($conditions['startDate'])) {
            $conditions['startDate'] = strtotime($conditions['startDate']);
        } else {
            unset($conditions['startDate']);
        }

        if (isset($conditions['endDate']) && !empty($conditions['endDate'])) {
            $conditions['endDate'] = strtotime($conditions['endDate']);
        } else {
            unset($conditions['endDate']);
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
                        unset($conditions['ids']);
                    }
                } else {
                    unset($conditions['ids']);
                }
            }

            if ($conditions['sourceFrom'] == 'favorite' && !isset($conditions['ids'])) {
                unset($conditions['ids']);
            }

            if ($conditions['sourceFrom'] != 'favorite') {
                if ($ids) {
                    $conditions['ids'] = $ids;
                } else {
                    unset($conditions['ids']);
                }
            }

            unset($conditions['tagId']);
        }

        return $conditions;
    }

    //================
    public function createUseFiles($fileIds, $targetId, $targetType, $type)
    {
        $fileIds    = empty($fileIds) ? array() : explode(",", $fileIds);
        $newFileIds = $this->findCreatedFileIds($fileIds, $targetType, $targetId);
        if (empty($newFileIds)) {
            return false;
        }

        $attachments = array_map(function ($fileId) use ($targetType, $targetId, $type) {
            $attachment = array(
                'fileId'      => $fileId,
                'targetType'  => $targetType,
                'targetId'    => $targetId,
                'type'        => $type,
                'createdTime' => time()
            );
            return $attachment;
        }, $newFileIds);

        foreach ($attachments as $attachment) {
            $this->getFileUsedDao()->create($attachment);
        }

        $files = $this->findFilesByIds($newFileIds);
        foreach ($files as $file) {
            $this->update($file['id'], array('useType' => $targetType, 'usedCount' => $file['usedCount'] + 1));
        }
    }

    public function findUseFilesByTargetTypeAndTargetIdAndType($targetType, $targetId, $type)
    {
        $conditions = array(
            'type'       => $type,
            'targetType' => $targetType,
            'targetId'   => $targetId
        );

        $limit       = $this->getFileUsedDao()->count($conditions);
        $attachments = $this->getFileUsedDao()->search($conditions, array('createdTime', 'DESC'), 0, $limit);
        $this->bindFiles($attachments);
        return $attachments;
    }

    public function searchUseFiles($conditions)
    {
        $limit       = $this->getFileUsedDao()->count($conditions);
        $attachments = $this->getFileUsedDao()->search($conditions, array('createdTime', 'DESC'), 0, $limit);
        $this->bindFiles($attachments);
        return $attachments;
    }

    public function getUseFile($id)
    {
        $attachment = $this->getFileUsedDao()->get($id);
        $this->bindFile($attachment);
        return $attachment;
    }

    public function deleteUseFile($id)
    {
        $attachment = $this->getFileUsedDao()->get($id);
        $file       = $this->getFile($attachment['fileId']);
        if (empty($file)) {
            $this->createNotFoundException("该附件不存在,或已被删除");
        }
        $fireWall = FireWallFactory::create($attachment['targetType']);
        if (!$fireWall->canAccess($attachment)) {
            $this->createAccessDeniedException("您无全删除该附件");
        }

        $this->getFileUsedDao()->getConnection()->beginTransaction();
        try {
            $this->getFileUsedDao()->delete($id);
            $this->deleteFile($file['id']);

            $this->getFileUsedDao()->getConnection()->commit();
        } catch (\Exception $e) {
            $this->getFileUsedDao()->getConnection()->rollback();
            throw $e;
        }
    }

    protected function findCreatedFileIds($fileIds, $targetType, $targetId)
    {
        $conditions = array(
            'targetType' => $targetType,
            'targetId'   => $targetId
        );
        $existUseFiles = $this->getFileUsedDao()->search($conditions, array('createdTime', 'DESC'), 0, 100);
        $existFileIds  = ArrayToolkit::column($existUseFiles, 'fileId');

        return array_diff($fileIds, $existFileIds);
    }

    /**
     * Impure Function
     * 每个attachment 增加key file
     * @param array $attachments
     */
    protected function bindFiles(array &$attachments)
    {
        $files = $this->getUploadFileDao()->findFilesByIds(ArrayToolkit::column($attachments, 'fileId'));
        if (!empty($files)) {
            $files = $this->getFileImplementor('cloud')->findFiles($files, array('resType' => 'attachment'));
        }

        $files = ArrayToolkit::index($files, 'id');
        foreach ($attachments as $key => &$attachment) {
            if (isset($files[$attachment['fileId']])) {
                $attachment['file'] = $files[$attachment['fileId']];
            } else {
                $this->getFileUsedDao()->delete($attachment['id']);
                unset($attachments[$key]);
            }
        }
    }

    /**
     * Impure Function
     * attachment 增加key file
     * @param $attachment
     */
    protected function bindFile(&$attachment)
    {
        $file = $this->getFile($attachment['fileId']);
        if (empty($file)) {
            unset($attachments);
        } else {
            $attachment['file'] = $file;
        }
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

    protected function getFileUsedDao()
    {
        return $this->createDao('File.FileUsedDao');
    }

    private function _checkOrderBy($order)
    {
        if (is_array($order)) {
            return $order;
        }

        $orderArray = array(
            'latestUpdated' => array('updatedTime', 'DESC'),
            'oldestUpdated' => array('updatedTime', 'ASC'),
            'latestCreated' => array('createdTime', 'DESC'),
            'oldestCreated' => array('createdTime', 'ASC'),
            'extAsc'        => array('ext', 'ASC'),
            'extDesc'       => array('ext', 'DESC'),
            'nameAsc'       => array('filename', 'ASC'),
            'nameDesc'      => array('filename', 'DESC'),
            'sizeAsc'       => array('fileSize', 'ASC'),
            'sizeDesc'      => array('fileSize', 'DESC')
        );

        if (in_array($order, $orderArray)) {
            return $orderArray[$order];
        } else {
            throw $this->createServiceException('参数sort不正确。');
        }
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
