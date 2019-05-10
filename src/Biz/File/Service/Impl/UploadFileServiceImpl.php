<?php

namespace Biz\File\Service\Impl;

use AppBundle\Common\CloudFileStatusToolkit;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\File\Dao\FileUsedDao;
use Biz\File\Dao\UploadFileDao;
use Biz\File\UploadFileException;
use Biz\User\Service\UserService;
use AppBundle\Common\ArrayToolkit;
use Biz\File\Dao\UploadFileTagDao;
use Biz\System\Service\LogService;
use Biz\File\Dao\UploadFileInitDao;
use Biz\File\Dao\UploadFileShareDao;
use Biz\Course\Service\CourseService;
use Biz\File\Service\FileImplementor;
use Biz\File\Dao\UploadFileCollectDao;
use Biz\File\FireWall\FireWallFactory;
use Biz\System\Service\SettingService;
use Biz\File\Service\UploadFileService;
use Biz\User\UserException;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Codeages\Biz\Framework\Event\Event;

class UploadFileServiceImpl extends BaseService implements UploadFileService
{
    public static $implementor
    = array(
        'local' => 'File:LocalFileImplementor',
        'cloud' => 'File:CloudFileImplementor',
    );

    /**
     * @return (opened, needOpen, notAllowed)
     */
    public function getAudioServiceStatus()
    {
        $setting = $this->getSettingService()->get('storage', array());

        if (!empty($setting['cloud_access_key']) || !empty($setting['cloud_secret_key'])) {
            $audioService = $this->getFileImplementor('cloud')->getAudioServiceStatus();
        }

        if (empty($audioService['audioService'])) {
            return 'notAllowed';
        }

        $enableAudioStatus = $this->getCourseService()->isSupportEnableAudio(true);
        if (!$enableAudioStatus && 'opened' == $audioService['audioService']) {
            return 'needOpen';
        }

        return $audioService['audioService'];
    }

    public function getFile($id)
    {
        $file = $this->getUploadFileDao()->get($id);

        if (empty($file)) {
            return null;
        }

        return $this->getFileImplementor($file['storage'])->getFile($file);
    }

    public function getFullFile($id)
    {
        $file = $this->getUploadFileDao()->get($id);

        if (empty($file)) {
            return null;
        }

        if (empty($file['globalId'])) {
            return $file;
        }

        return $this->getFileImplementor($file['storage'])->getFullFile($file);
    }

    public function batchConvertByIds($ids)
    {
        if (empty($ids)) {
            return false;
        }

        $conditions = array(
            'ids' => $ids,
            'type' => 'video',
            'storage' => 'cloud',
            'inAudioConvertStatus' => array('none', 'error'),
        );

        $count = $this->getUploadFileDao()->count($conditions);

        for ($start = 0; $start < $count; $start = $start + 100) {
            $videofiles = $this->getUploadFileDao()->search($conditions, null, $start, 100);

            $this->retryTranscode(ArrayToolkit::column($videofiles, 'globalId'));
        }
        $this->getUploadFileDao()->update($conditions, array('audioConvertStatus' => 'doing'));

        return true;
    }

    //视频转音频的完成情况
    public function getAudioConvertionStatus($ids)
    {
        if (empty($ids)) {
            return '0';
        }

        $completedCount = $this->getUploadFileDao()->count(array(
            'ids' => $ids,
            'type' => 'video',
            'storage' => 'cloud',
            'audioConvertStatus' => 'success',
        ));

        return sprintf('%d/%d', $completedCount, count($ids));
    }

    public function getUploadFileInit($id)
    {
        return $this->getUploadFileInitDao()->get($id);
    }

    public function getFileByGlobalId($globalId)
    {
        $file = $this->getUploadFileDao()->getByGlobalId($globalId);

        if (empty($file)) {
            return null;
        }

        return $this->getFileImplementor($file['storage'])->getFullFile($file);
    }

    public function findFilesByIds(array $ids, $showCloud = 0, $params = array())
    {
        $files = $this->getUploadFileDao()->findByIds($ids);

        if (empty($files)) {
            return array();
        }

        if ($showCloud) {
            $files = $this->getFileImplementor('cloud')->findFiles($files, $params);
        }

        return $files;
    }

    public function findFilesByTargetTypeAndTargetIds($targetType, $targetIds)
    {
        return $this->getUploadFileDao()->findByTargetTypeAndTargetIds($targetType, $targetIds);
    }

    public function update($fileId, $fields)
    {
        $file = $this->getUploadFileDao()->get($fileId);

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
                return $this->getUploadFileDao()->update($file['id'], $fields);
            }
        }

        return false;
    }

    public function sharePublic($id)
    {
        return $this->getUploadFileDao()->update($id, array('isPublic' => 1));
    }

    public function unsharePublic($id)
    {
        return $this->getUploadFileDao()->update($id, array('isPublic' => 0));
    }

    public function getDownloadMetas($id, $ssl = false)
    {
        $file = $this->getUploadFileDao()->get($id);

        if (empty($file)) {
            return array('error' => 'not_found', 'message' => '文件不存在，不能下载！');
        }

        return $this->getFileImplementor($file['storage'])->getDownloadFile($file, $ssl);
    }

    public function getUploadAuth($params)
    {
        $user = $this->getCurrentUser();

        if (empty($user)) {
            $this->createNewException(UserException::UN_LOGIN());
        }

        $setting = $this->getSettingService()->get('storage');
        $params['storage'] = empty($setting['upload_mode']) ? 'local' : $setting['upload_mode'];

        $implementor = $this->getFileImplementor($params['storage']);

        $auth = $implementor->getUploadAuth($params);

        return $auth;
    }

    /**
     * 传入的参数：
     *
     *   targetId 目标Id
     *   targetType 目标类型
     *   hash 文件hash
     *   fileName 文件名称
     *   fileSize 文件大小
     *   uploadType 上传类型（direct表示直传不转码）
     *
     * @param array $params
     *
     * @throws
     *
     * @return array
     *               globalId
     *               Url 上传地址
     *               token 上传token
     *               fileId  upload_init id
     */
    public function initFormUpload($params)
    {
        $user = $this->getCurrentUser();

        if (empty($user)) {
            throw $this->createServiceException('用户未登录，上传初始化失败！');
        }

        if (!ArrayToolkit::requireds($params, array('targetId', 'targetType', 'hash'))) {
            throw $this->createServiceException('参数缺失，上传初始化失败！');
        }
        $params['userId'] = $user['id'];
        $params = ArrayToolkit::parts($params, array(
            'id',
            'userId',
            'targetId',
            'targetType',
            'bucket',
            'hash',
            'fileSize',
            'fileName',
            'uploadType',
        ));

        $setting = $this->getSettingService()->get('storage');
        $params['storage'] = empty($setting['upload_mode']) ? 'local' : $setting['upload_mode'];
        $implementor = $this->getFileImplementor($params['storage']);

        if (isset($params['id'])) {
            $file = $this->getUploadFileInitDao()->get($params['id']);
            $initParams = $implementor->resumeUpload($file, $params);

            if ('ok' == $initParams['resumed'] && $file && 'ok' != $file['status']) {
                $this->getUploadFileInitDao()->update($file['id'], array(
                    'filename' => $params['fileName'],
                    'fileSize' => $params['fileSize'],
                    'targetId' => $params['targetId'],
                    'targetType' => $params['targetType'],
                ));

                return $initParams;
            }
        }

        $preparedFile = $implementor->prepareUpload($params);
        $file = $this->getUploadFileInitDao()->create($preparedFile);
        $params = array_merge($params, $file);
        $initParams = $implementor->initFormUpload($params);

        if ('cloud' == $params['storage']) {
            $file = $this->getUploadFileInitDao()->update($file['id'], array('globalId' => $initParams['globalId']));
        }

        $this->getLogger()->info("initFormUpload 上传文件： #{$file['id']}");

        return $initParams;
    }

    public function initUpload($params)
    {
        $user = $this->getCurrentUser();

        if (empty($user)) {
            $this->createNewException(UserException::UN_LOGIN());
        }

        if (!ArrayToolkit::requireds($params, array('targetId', 'targetType', 'hash'))) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $params['userId'] = $user['id'];
        $params = ArrayToolkit::parts($params, array(
            'id',
            'directives',
            'userId',
            'targetId',
            'targetType',
            'bucket',
            'hash',
            'fileSize',
            'fileName',
            'watermarks',
        ));

        $setting = $this->getSettingService()->get('storage');
        $params['storage'] = empty($setting['upload_mode']) ? 'local' : $setting['upload_mode'];
        $implementor = $this->getFileImplementor($params['storage']);

        if (isset($params['id'])) {
            $file = $this->getUploadFileInitDao()->get($params['id']);
            $initParams = $implementor->resumeUpload($file, $params);

            if ('ok' == $initParams['resumed'] && $file && 'ok' != $file['status']) {
                $this->getUploadFileInitDao()->update($file['id'], array(
                    'filename' => $params['fileName'],
                    'fileSize' => $params['fileSize'],
                    'targetId' => $params['targetId'],
                    'targetType' => $params['targetType'],
                ));

                return $initParams;
            }
        }

        $preparedFile = $implementor->prepareUpload($params);
        $file = $this->getUploadFileInitDao()->create($preparedFile);
        $params = array_merge($params, $file);
        $initParams = $implementor->initUpload($params);

        if ('cloud' == $params['storage']) {
            $file = $this->getUploadFileInitDao()->update($file['id'], array('globalId' => $initParams['globalId']));
        }

        $this->getLogger()->info("initUpload 上传文件： #{$file['id']}");

        return $initParams;
    }

    public function finishedUpload($params)
    {
        $this->beginTransaction();
        try {
            $setting = $this->getSettingService()->get('storage');
            $params['storage'] = empty($setting['upload_mode']) ? 'local' : $setting['upload_mode'];

            if (empty($params['length'])) {
                $params['length'] = 0;
            }

            $implementor = $this->getFileImplementor($params['storage']);

            $fields = array(
                'status' => 'ok',
                'convertStatus' => 'none',
                'length' => $params['length'],
                'fileSize' => $params['size'],
            );

            $file = $this->getUploadFileInitDao()->update($params['id'], array('status' => 'ok'));

            if ('cloud' == $file['storage'] && 'video' == $file['type']) {
                $fields['audioConvertStatus'] = 'doing';
            }

            if (isset($params['uploadType']) && 'direct' == $params['uploadType']) {
                unset($fields['audioConvertStatus']);
            }

            $file = array_merge($file, $fields);

            $file = $this->getUploadFileDao()->create($file);

            $result = $implementor->finishedUpload($file, $params);

            if (empty($result) || !$result['success']) {
                $this->createNewException(UploadFileException::UPLOAD_FAILED());
            }

            $file = $this->getUploadFileDao()->update($file['id'], array(
                'length' => isset($result['length']) ? $result['length'] : 0,
            ));

            $this->getLogger()->info("finishedUpload 添加文件：#{$file['id']}");

            if ('headLeader' == $file['targetType']) {
                $headLeaders = $this->getUploadFileDao()->findHeadLeaderFiles();

                foreach ($headLeaders as $headLeader) {
                    if ($headLeader['id'] != $file['id']) {
                        $this->deleteFile($headLeader['id']);
                    }
                }
            }

            $this->dispatchEvent('upload.file.finish', array('file' => $file));

            $this->commit();

            return $file;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function moveFile($targetType, $targetId, $originalFile = null, $data = array())
    {
        return $this->getFileImplementor('local')->moveFile($targetType, $targetId, $originalFile, $data);
    }

    public function setFileProcessed($params)
    {
        try {
            $file = $this->getUploadFileInitDao()->getByGlobalId($params['globalId']);

            $fields = array(
                'convertStatus' => 'success',
            );

            $this->getUploadFileInitDao()->update($file['id'], $fields);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function deleteByGlobalId($globalId)
    {
        $file = $this->getUploadFileDao()->getByGlobalId($globalId);

        if (empty($file)) {
            return null;
        }

        $result = $this->getUploadFileDao()->deleteByGlobalId($globalId);

        return $result;
    }

    public function searchShareHistoryCount($conditions)
    {
        return $this->getUploadFileShareDao()->count($conditions);
    }

    public function searchShareHistories($conditions, $orderBy, $start, $limit)
    {
        return $this->getUploadFileShareDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function findActiveShareHistory($sourceUserId)
    {
        $shareHistories = $this->getUploadFileShareDao()->findActiveByUserId($sourceUserId);

        return $shareHistories;
    }

    public function reconvertFile($id, $options = array())
    {
        $file = $this->getFile($id);

        if (empty($file)) {
            $this->createNewException(UploadFileException::NOTFOUND_FILE());
        }

        $convertHash = $this->getFileImplementor($file['storage'])->reconvert($file['globalId'], $options);

        return $convertHash;
    }

    public function reconvertOldFile($id, $convertCallback, $pipeline)
    {
        $result = array();

        $file = $this->getFile($id);

        if (empty($file)) {
            return array('error' => 'file_not_found', 'message' => sprintf('文件%s，不存在。', $id));
        }

        if ('cloud' != $file['storage']) {
            return array('error' => 'not_cloud_file', 'message' => sprintf('文件%s，不是云文件。', $id));
        }

        if ('video' != $file['type']) {
            return array('error' => 'not_video_file', 'message' => sprintf('文件%s，不是视频文件。', $id));
        }

        if ('courselesson' != $file['targetType']) {
            return array('error' => 'not_course_file', 'message' => sprintf('文件%s，不是课时文件。', $id));
        }

        $target = $this->getCourseService()->getCourse($file['targetId']);

        if (empty($target)) {
            return array('error' => 'course_not_exist', 'message' => sprintf('文件%s所属的课程已删除。', $id));
        }

        if (!empty($file['convertParams']['convertor']) && 'HLSEncryptedVideo' == $file['convertParams']['convertor']) {
            return array('error' => 'already_converted', 'message' => sprintf('文件%s已转换', $id));
        }

        $fileNeedUpdateFields = array();

        if (!empty($file['convertParams']['convertor']) && 'HLSVideo' == $file['convertParams']['convertor']) {
            $file['convertParams']['hlsKeyUrl'] = 'http://hlskey.edusoho.net/placeholder';
            $file['convertParams']['hlsKey'] = $this->generateKey(16);

            if ('low' == $file['convertParams']['videoQuality']) {
                $file['convertParams']['videoQuality'] = 'normal';
                $file['convertParams']['video'] = array('440k', '640k', '1000K');
            }

            $fileNeedUpdateFields['convertParams'] = json_encode($file['convertParams']);
            $file['convertParams']['convertor'] = 'HLSEncryptedVideo';
        }

        if (empty($file['convertParams'])) {
            $convertParams = array(
                'convertor' => 'HLSEncryptedVideo',
                'segtime' => 10,
                'videoQuality' => 'normal',
                'audioQuality' => 'normal',
                'video' => array('440k', '640k', '1000K'),
                'audio' => array('48k', '64k', '96k'),
                'hlsKeyUrl' => 'http://hlskey.edusoho.net/placeholder',
                'hlsKey' => $this->generateKey(16),
            );

            $file['convertParams'] = $convertParams;

            $convertParams['convertor'] = 'HLSVideo';
            $fileNeedUpdateFields['convertParams'] = json_encode($convertParams);
        }

        $convertHash = $this->getFileImplementor($file['storage'])->reconvertOldFile($file, $convertCallback, $pipeline);

        if (empty($convertHash)) {
            return array('error' => 'convert_request_failed', 'message' => sprintf('文件%s转换请求失败！', $id));
        }

        $fileNeedUpdateFields['convertHash'] = $convertHash;
        $fileNeedUpdateFields['updatedTime'] = time();

        $this->getUploadFileDao()->update($file['id'], $fileNeedUpdateFields);

        return $result;
        /*$subTarget = $this->getCourseService()->findLessonsByTypeAndMediaId('video', $file['id']) ?: array();

    if (!empty($subTarget)) {
    $subTarget = $subTarget[0];
    }

    return array(
    'convertHash' => $convertHash,
    'courseId'    => empty($subTarget['courseId']) ? $target['targetId'] : $subTarget['courseId'],
    'lessonId'    => empty($subTarget['id']) ? 0 : $subTarget['id']
    );*/
    }

    public function retryTranscode(array $globalIds)
    {
        return $this->getFileImplementor('cloud')->retryTranscode($globalIds);
    }

    public function getResourcesStatus(array $options)
    {
        return $this->getFileImplementor('cloud')->getResourcesStatus($options);
    }

    public function collectFile($userId, $fileId)
    {
        if (empty($userId) || empty($fileId)) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }

        $collection = $this->getUploadFileCollectDao()->getByUserIdAndFileId($userId, $fileId);

        if (empty($collection)) {
            $collection = array(
                'userId' => $userId,
                'fileId' => $fileId,
                'updatedTime' => time(),
                'createdTime' => time(),
            );
            $collection = $this->getUploadFileCollectDao()->create($collection);
            $result = $this->getUploadFileDao()->get($collection['fileId']);

            return $result;
        }

        $this->getUploadFileCollectDao()->delete($collection['id']);

        return false;
    }

    public function findCollectionsByUserIdAndFileIds($fileIds, $userId)
    {
        if (empty($fileIds)) {
            return array();
        }

        $collections = $this->getUploadFileCollectDao()->findByUserIdAndFileIds($fileIds, $userId);

        return $collections;
    }

    public function findCollectionsByUserId($userId)
    {
        $collections = $this->getUploadFileCollectDao()->findByUserId($userId);

        return $collections;
    }

    public function syncFile($file)
    {
        return $file;
    }

    public function getFileByHashId($hashId)
    {
        $file = $this->getUploadFileDao()->getByHashId($hashId);

        if (empty($file)) {
            return null;
        }

        return $this->getFileImplementor($file['storage'])->getFile($file);
    }

    public function getFileByConvertHash($hash)
    {
        return $this->getUploadFileDao()->getByConvertHash($hash);
    }

    public function searchCloudFilesFromLocal($conditions, $orderBy, $start, $limit)
    {
        $conditions = $this->_prepareSearchConditions($conditions);

        $files = $this->getUploadFileDao()->search($conditions, $orderBy, $start, $limit);
        if (empty($files)) {
            return array();
        }

        $cloudFileConditions = array();
        if (isset($conditions['resType'])) {
            $cloudFileConditions['resType'] = $conditions['resType'];
        }
        $cloudFiles = $this->getFileImplementor('cloud')->findFiles($files, $cloudFileConditions);
        $files = ArrayToolkit::index($files, 'id');
        foreach ($cloudFiles as &$cloudFile) {
            $cloudFile['type'] = $files[$cloudFile['id']]['type'];
        }

        return $cloudFiles;
    }

    public function countCloudFilesFromLocal($conditions)
    {
        $conditions = $this->_prepareSearchConditions($conditions);

        return $this->getUploadFileDao()->count($conditions);
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

    public function searchUploadFiles($conditions, $orderBy, $start, $limit)
    {
        return $this->getUploadFileDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function countUploadFiles($conditions)
    {
        return $this->getUploadFileDao()->count($conditions);
    }

    protected function searchFilesFromCloud($conditions, $orderBy, $start, $limit)
    {
        $files = $this->getUploadFileDao()->search($conditions, $orderBy, 0, PHP_INT_MAX);
        $globalIds = ArrayToolkit::column($files, 'globalId');

        if (empty($globalIds)) {
            return array();
        }

        $cloudFileConditions = array(
            'processStatus' => $conditions['processStatus'],
            'nos' => implode(',', $globalIds),
            'start' => $start,
            'limit' => $limit,
        );

        if (isset($conditions['errorType'])) {
            $cloudFileConditions['errorType'] = $conditions['errorType'];
        }

        if (isset($conditions['resType'])) {
            $cloudFileConditions['resType'] = $conditions['resType'];
        }

        $cloudFiles = $this->getFileImplementor('cloud')->search($cloudFileConditions);

        return $cloudFiles['data'];
    }

    protected function searchFilesFromLocal($conditions, $orderBy, $start, $limit)
    {
        $files = $this->getUploadFileDao()->search($conditions, $orderBy, $start, $limit);
        if (empty($files)) {
            return array();
        }

        $groupFiles = ArrayToolkit::group($files, 'storage');

        if (isset($groupFiles['cloud']) && !empty($groupFiles['cloud'])) {
            $cloudFileConditions = array(
                'nos' => implode(',', ArrayToolkit::column($groupFiles['cloud'], 'globalId')),
            );
            if (isset($conditions['resType'])) {
                $cloudFileConditions['resType'] = $conditions['resType'];
            }
            $cloudFiles = $this->getFileImplementor('cloud')->findFiles($groupFiles['cloud'], $cloudFileConditions);
            $cloudFiles = ArrayToolkit::index($cloudFiles, 'id');

            foreach ($files as $key => $file) {
                if ('cloud' == $file['storage']) {
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
            return $this->getUploadFileDao()->count($conditions);
        }
    }

    public function searchFileCountFromCloud($conditions)
    {
        $files = $this->getUploadFileDao()->search($conditions, array('createdTime' => 'DESC'), 0, PHP_INT_MAX);
        $globalIds = ArrayToolkit::column($files, 'globalId');

        if (empty($globalIds)) {
            return 0;
        }

        $cloudFileConditions = array(
            'processStatus' => $conditions['processStatus'],
        );

        if (isset($conditions['errorType'])) {
            $cloudFileConditions['errorType'] = $conditions['errorType'];
        }

        $globalArray = array_chunk($globalIds, 20);
        $count = 0;

        foreach ($globalArray as $key => $globals) {
            $cloudFileConditions['nos'] = implode(',', $globals);

            $cloudFiles = $this->getFileImplementor('cloud')->search($cloudFileConditions);
            $count += $cloudFiles['count'];
        }

        return $count;
    }

    public function addFile($targetType, $targetId, array $fileInfo = array(), $implemtor = 'local', UploadedFile $originalFile = null)
    {
        $this->beginTransaction();
        try {
            $file = $this->getFileImplementor($implemtor)->addFile($targetType, $targetId, $fileInfo, $originalFile);

            if ('cloud' == $implemtor) {
                $fileInit = $this->getUploadFileInitDao()->create($file);
                $file['id'] = $fileInit['id'];
            }

            $file = $this->getUploadFileDao()->create($file);

            $this->dispatchEvent('upload.file.add', array('file' => $file));
            $this->getLogger()->info("addFile 添加文件：#{$file['id']}");

            $this->commit();

            return $file;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function renameFile($id, $newFilename)
    {
        $this->getUploadFileDao()->update($id, array('filename' => $newFilename));

        return $this->getFile($id);
    }

    public function deleteFile($id)
    {
        $file = $this->getFile($id);

        if (empty($file)) {
            return false;
        }

        $result = $this->getFileImplementor($file['storage'])->deleteFile($file);

        //XXX
        //1. 可能由于异常或脏数据、事务回滚导致资源不存在的情况，对edusoho来说，不应影响本地文件记录的删除
        //2. 云API没有针对上述错误提供错误码，因此根据返回的错误信息进行判断，以后应当优化
        if ((isset($result['success']) && $result['success'])
          || (!empty($result['error']) && '资源不存在，或已删除！' == $result['error'])
        ) {
            $result = $this->getUploadFileDao()->delete($id);
        }

        $this->dispatchEvent('upload.file.delete', $file);

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
            $this->createNewException(UploadFileException::NOTFOUND_FILE());
        }

        $file = $this->getFileImplementor($file['storage'])->saveConvertResult($file, $result);

        $this->getUploadFileDao()->update($id, array(
            'convertStatus' => $file['convertStatus'],
            'metas2' => json_encode($file['metas2']),
            'updatedTime' => time(),
        ));

        return $this->getFile($id);
    }

    public function saveConvertResult3($id, array $result = array())
    {
        $file = $this->getFile($id);

        if (empty($file)) {
            $this->createNewException(UploadFileException::NOTFOUND_FILE());
        }

        $file['convertParams']['convertor'] = 'HLSEncryptedVideo';

        $fileNeedUpdateFields = array();

        $file = $this->getFileImplementor($file['storage'])->saveConvertResult($file, $result);

        if ('success' == $file['convertStatus']) {
            $fileNeedUpdateFields['convertParams'] = json_encode($file['convertParams']);
            $fileNeedUpdateFields['metas2'] = json_encode($file['metas2']);
            $fileNeedUpdateFields['updatedTime'] = time();
            $this->getUploadFileDao()->update($id, $fileNeedUpdateFields);
        }

        return $this->getFile($id);
    }

    public function convertFile($id, $status, array $result = array(), $callback = null)
    {
        $statuses = array('none', 'waiting', 'doing', 'success', 'error');

        if (!in_array($status, $statuses)) {
            $this->createNewException(UploadFileException::ERROR_STATUS());
        }

        $file = $this->getFile($id);

        if (empty($file)) {
            $this->createNewException(UploadFileException::NOTFOUND_FILE());
        }

        $file = $this->getFileImplementor($file['storage'])->convertFile($file, $status, $result, $callback);

        $this->getUploadFileDao()->update($id, array(
            'convertStatus' => $file['convertStatus'],
            'metas2' => $file['metas2'],
            'updatedTime' => time(),
        ));

        return $this->getFile($id);
    }

    public function setFileConverting($id, $convertHash)
    {
        $file = $this->getFile($id);

        if (empty($file)) {
            $this->createNewException(UploadFileException::NOTFOUND_FILE());
        }

        // $status = $file['convertStatus'] == 'success' ? 'success' : 'waiting';

        $fields = array(
            'convertStatus' => 'waiting',
            'convertHash' => $convertHash,
            'updatedTime' => time(),
        );
        $this->getUploadFileDao()->update($id, $fields);

        return $this->getFile($id);
    }

    public function setAudioConvertStatus($id, $status)
    {
        $file = $this->getFile($id);

        if (empty($file)) {
            $this->createNewException(UploadFileException::NOTFOUND_FILE());
        }

        if (!in_array($status, array('none', 'doing', 'success', 'error'))) {
            $this->createNewException(UploadFileException::ERROR_STATUS());
        }

        $fields = array(
            'audioConvertStatus' => $status,
            'updatedTime' => time(),
        );
        $this->getUploadFileDao()->update($id, $fields);

        return $this->getFile($id);
    }

    public function setResourceConvertStatus($globalId, array $result)
    {
        $file = $this->getUploadFileDao()->getByGlobalId($globalId);

        if (empty($file)) {
            return array();
        }

        $convertStatus = CloudFileStatusToolkit::convertProcessStatus($result['status']);

        if ('error' == $convertStatus && isset($result['errorType']) && 'client' == $result['errorType']) {
            $convertStatus = 'nonsupport';
        }

        $fields = array(
            'convertStatus' => $convertStatus,
            'audioConvertStatus' => 'none',
            'mp4ConvertStatus' => 'none',
            'updatedTime' => time(),
        );

        if ($result['audio']) {
            $fields['audioConvertStatus'] = $convertStatus;
        }

        if ($result['mp4']) {
            $fields['mp4ConvertStatus'] = $convertStatus;
        }

        return $this->getUploadFileDao()->update($file['id'], $fields);
    }

    public function makeUploadParams($params)
    {
        return $this->getFileImplementor($params['storage'])->makeUploadParams($params);
    }

    public function getFileByTargetType($targetType)
    {
        $file = $this->getUploadFileDao()->getByTargetType($targetType);

        if (empty($file)) {
            return null;
        }

        return $this->getFileImplementor($file['storage'])->getFullFile($file);
    }

    public function tryManageFile($fileId)
    {
        $user = $this->getCurrentUser();

        if (!$user->isTeacher()) {
            $this->createNewException(UserException::PERMISSION_DENIED());
        }

        $file = $this->getFullFile($fileId);

        if (empty($file)) {
            $this->createNewException(UploadFileException::NOTFOUND_FILE());
        }

        if ($user->isAdmin()) {
            return $file;
        }

        if (!$user->isAdmin() && $user['id'] != $file['createdUserId']) {
            $this->createNewException(UploadFileException::PERMISSION_DENIED());
        }

        return $file;
    }

    public function tryManageGlobalFile($globalFileId)
    {
        $user = $this->getCurrentUser();

        if (!$user->isTeacher()) {
            $this->createNewException(UserException::PERMISSION_DENIED());
        }

        $file = $this->getFileByGlobalId($globalFileId);

        if (empty($file)) {
            $this->createNewException(UploadFileException::NOTFOUND_FILE());
        }

        if ($user->isAdmin()) {
            return $file;
        }

        if (!$user->isAdmin() && $user['id'] != $file['createdUserId']) {
            $this->createNewException(UploadFileException::PERMISSION_DENIED());
        }

        return $file;
    }

    public function tryAccessFile($fileId)
    {
        $file = $this->getFullFile($fileId);

        if (empty($file)) {
            $this->createNewException(UploadFileException::NOTFOUND_FILE());
        }

        $user = $this->getCurrentUser();

        if ($user->isAdmin()) {
            return $file;
        }

        if (1 == $file['isPublic']) {
            return $file;
        }

        if ($file['createdUserId'] == $user['id']) {
            return $file;
        }

        $shares = $this->findShareHistory($file['createdUserId']);

        $targetUserIds = ArrayToolkit::column($shares, 'targetUserId');
        if (in_array($user['id'], $targetUserIds)) {
            return $file;
        }
        $this->createNewException(UploadFileException::PERMISSION_DENIED());
    }

    public function canManageFile($fileId)
    {
        $user = $this->getCurrentUser();
        $file = $this->getFullFile($fileId);

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isTeacher()) {
            return true;
        }

        if ($user['id'] != $file['createdUserId']) {
            return false;
        }

        return true;
    }

    public function findMySharingContacts($targetUserId)
    {
        $userIds = $this->getUploadFileShareDao()->findByTargetUserIdAndIsActive($targetUserId);

        if (!empty($userIds)) {
            return $this->getUserService()->findUsersByIds(ArrayToolkit::column($userIds, 'sourceUserId'));
        } else {
            return null;
        }
    }

    public function findShareHistory($sourceUserId)
    {
        return $this->getUploadFileShareDao()->findByUserId($sourceUserId);
    }

    public function shareFiles($sourceUserId, $targetUserIds)
    {
        foreach ($targetUserIds as $targetUserId) {
            if ($targetUserId != $sourceUserId) {
                $shareHistory = $this->getUploadFileShareDao()->findBySourceUserIdAndTargetUserId($sourceUserId, $targetUserId);

                if (!empty($shareHistory)) {
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
        return $this->getUploadFileShareDao()->findBySourceUserIdAndTargetUserId($sourceUserId, $targetUserId);
    }

    public function addShare($sourceUserId, $targetUserId)
    {
        $fileShareFields = array(
            'sourceUserId' => $sourceUserId,
            'targetUserId' => $targetUserId,
            'isActive' => 1,
            'createdTime' => time(),
            'updatedTime' => time(),
        );

        return $this->getUploadFileShareDao()->create($fileShareFields);
    }

    public function updateShare($shareHistoryId)
    {
        $fileShareFields = array(
            'isActive' => 1,
            'updatedTime' => time(),
        );

        return $this->getUploadFileShareDao()->update($shareHistoryId, $fileShareFields);
    }

    public function cancelShareFile($sourceUserId, $targetUserId)
    {
        $shareHistory = $this->getUploadFileShareDao()->findBySourceUserIdAndTargetUserId($sourceUserId, $targetUserId);

        if (!empty($shareHistory)) {
            $fileShareFields = array(
                'isActive' => 0,
                'updatedTime' => time(),
            );

            $this->getUploadFileShareDao()->update($shareHistory['id'], $fileShareFields);
        }
    }

    public function waveUploadFile($id, $field, $diff)
    {
        return $this->getUploadFileDao()->waveUsedCount($id, $diff);
    }

    public function waveUsedCount($id, $num)
    {
        return $this->getUploadFileDao()->waveUsedCount($id, $num);
    }

    protected function updateTags($localFile, $fields)
    {
        if (!empty($fields['tags'])) {
            $tagNames = explode(',', $fields['tags']);
            $this->getUploadFileTagDao()->deleteByFileId($localFile['id']);

            foreach ($tagNames as $tagName) {
                $tag = $this->getTagService()->getTagByName($tagName);
                $this->getUploadFileTagDao()->create(array('tagId' => $tag['id'], 'fileId' => $localFile['id']));
            }
        } else {
            $this->getUploadFileTagDao()->deleteByFileId($localFile['id']);
        }
    }

    protected function _prepareSearchConditions($conditions)
    {
        if ($this->hasProcessStatusCondition($conditions)) {
            $conditions['storage'] = 'cloud';
            $conditions['existGlobalId'] = 0;
            $conditions = array_merge($conditions, CloudFileStatusToolkit::getTranscodeFilterStatusCondition($conditions['processStatus']));
        }

        if (!empty($conditions['startDate'])) {
            $conditions['startDate'] = strtotime($conditions['startDate']);
        }

        if (!empty($conditions['endDate'])) {
            $conditions['endDate'] = strtotime($conditions['endDate']);
        }

        if (!empty($conditions['useStatus']) && 'unused' == $conditions['useStatus']) {
            $conditions['endCount'] = 1;
        }

        if (!empty($conditions['useStatus']) && 'used' == $conditions['useStatus']) {
            $conditions['startCount'] = 1;
        }

        $conditions = $this->filterKeyWords($conditions);
        $conditions = $this->filterSourceForm($conditions);
        $conditions = $this->filterTag($conditions);

        return $conditions;
    }

    protected function filterKeyWords($conditions)
    {
        if (!empty($conditions['keywordType']) && !empty($conditions['keyword'])) {
            $keywordType = $conditions['keywordType'];
            $keyword = $conditions['keyword'];

            if ('course' == $keywordType) {
                $courseSets = $this->getCourseSetService()->findCourseSetsLikeTitle($keyword);
                if (empty($courseSets)) {
                    $conditions['ids'] = array(-1);
                } else {
                    $courseSetIds = ArrayToolkit::column($courseSets, 'id');
                    $courseMaterials = $this->getMaterialService()->searchMaterials(
                        array('courseSetIds' => $courseSetIds),
                        array('createdTime' => 'DESC'),
                        0,
                        PHP_INT_MAX
                    );
                    $fileIds = ArrayToolkit::column($courseMaterials, 'fileId');
                    $fileIds = empty($fileIds) ? array(-1) : $fileIds;

                    if (!empty($conditions['ids'])) {
                        $intersect = array_intersect($conditions['ids'], $fileIds);
                        $conditions['ids'] = empty($intersect) ? array(-1) : $intersect;
                    } else {
                        $conditions['ids'] = $fileIds;
                    }
                }
            } elseif ('title' == $keywordType) {
                $conditions['filenameLike'] = $keyword;
            }
        }

        unset($conditions['keywordType']);
        unset($conditions['keyword']);

        return $conditions;
    }

    protected function filterSourceForm($conditions)
    {
        $user = $this->getCurrentuser();
        $sourceFrom = empty($conditions['sourceFrom']) ? '' : $conditions['sourceFrom'];

        switch ($sourceFrom) {
            case 'my':
                $conditions['createdUserIds'] = array($user['id']);
                break;
            case 'public':
                $conditions['isPublic'] = 1;
                break;
            case 'favorite':
                $collections = $this->findCollectionsByUserId($user['id']);
                $fileIds = ArrayToolkit::column($collections, 'fileId');
                $conditions['ids'] = $fileIds ? $fileIds : array(0);
                break;
            case 'sharing':
                $fromSharing = $this->getUploadFileShareDao()->findByTargetUserIdAndIsActive($user['id'], 1);
                $sourceUserIds = ArrayToolkit::column($fromSharing, 'sourceUserId');
                $conditions['createdUserIds'] = empty($sourceUserIds) ? array(0) : $sourceUserIds;
                break;
            default:
                break;
        }
        unset($conditions['sourceFrom']);

        return $conditions;
    }

    protected function filterTag($conditions)
    {
        if (empty($conditions['tagId'])) {
            return $conditions;
        }

        $files = $this->getUploadFileTagDao()->findByTagId($conditions['tagId']);
        $ids = ArrayToolkit::column($files, 'fileId');

        if (empty($ids)) {
            $conditions['ids'] = array(0);

            return $conditions;
        }

        if (!empty($conditions['ids'])) {
            $intersect = array_intersect($conditions['ids'], $ids);
            $conditions['ids'] = empty($intersect) ? array(0) : $intersect;
        } else {
            $conditions['ids'] = $ids;
        }

        unset($conditions['tagId']);

        return $conditions;
    }

    public function createUseFiles($fileIds, $targetId, $targetType, $type)
    {
        if (empty($fileIds)) {
            return;
        }

        if ($fileIds && is_string($fileIds)) {
            $fileIds = explode(',', $fileIds);
        }

        $newFileIds = $this->findCreatedFileIds($fileIds, $targetType, $targetId);
        if (empty($newFileIds)) {
            $conditions = array(
                'fileIds' => $fileIds,
                'targetType' => $targetType,
                'targetId' => $targetId,
            );

            return $this->getFileUsedDao()->search($conditions, array('createdTime' => 'DESC'), 0, PHP_INT_MAX);
        }

        $attachments = array_map(function ($fileId) use ($targetType, $targetId, $type) {
            $attachment = array(
                'fileId' => $fileId,
                'targetType' => $targetType,
                'targetId' => $targetId,
                'type' => $type,
                'createdTime' => time(),
            );

            return $attachment;
        }, $newFileIds);

        $newAttachments = array();
        foreach ($attachments as $attachment) {
            $newAttachments[] = $this->getFileUsedDao()->create($attachment);
        }

        $files = $this->findFilesByIds($newFileIds);
        foreach ($files as $file) {
            $this->update($file['id'], array('useType' => $targetType, 'usedCount' => $file['usedCount'] + 1));
        }

        return $newAttachments;
    }

    public function batchCreateUseFiles($useFiles)
    {
        if (empty($useFiles)) {
            return;
        }

        return $this->getFileUsedDao()->batchCreate($useFiles);
    }

    public function findUseFilesByTargetTypeAndTargetIdAndType($targetType, $targetId, $type, $bindFile = true)
    {
        $conditions = array(
            'type' => $type,
            'targetType' => $targetType,
            'targetId' => $targetId,
        );

        $limit = $this->getFileUsedDao()->count($conditions);
        $attachments = $this->getFileUsedDao()->search($conditions, array('createdTime' => 'DESC'), 0, $limit);
        if ($bindFile) {
            $this->bindFiles($attachments);
        }

        return $attachments;
    }

    public function countUseFile($conditions)
    {
        return $this->getFileUsedDao()->count($conditions);
    }

    public function searchUseFiles($conditions, $bindFile = true, $sort = array('createdTime' => 'DESC'))
    {
        $limit = $this->countUseFile($conditions);
        $attachments = $this->getFileUsedDao()->search($conditions, $sort, 0, $limit);

        if ($bindFile) {
            $this->bindFiles($attachments);
        }

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

        $fireWall = $this->getFireWallFactory()->create($attachment['targetType']);

        if (!$fireWall->canAccess($attachment)) {
            $this->createAccessDeniedException('您无权删除该附件');
        }

        $this->beginTransaction();
        try {
            $this->getFileUsedDao()->delete($id);

            //如果附件多处被引用，则仅在删除最后的引用时删除附件
            $fileRefs = $this->getFileUsedDao()->count(array('fileId' => $attachment['fileId']));

            if (empty($fileRefs)) {
                $this->deleteFile($attachment['fileId']);
            }

            $this->dispatchEvent('delete.use.file', new Event($attachment));

            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    protected function findCreatedFileIds($fileIds, $targetType, $targetId)
    {
        $conditions = array(
            'targetType' => $targetType,
            'targetId' => $targetId,
        );
        $existUseFiles = $this->getFileUsedDao()->search($conditions, array('createdTime' => 'DESC'), 0, PHP_INT_MAX);
        $existFileIds = ArrayToolkit::column($existUseFiles, 'fileId');

        return array_diff($fileIds, $existFileIds);
    }

    /**
     * Impure Function
     * 每个attachment 增加key file.
     *
     * @param array $attachments
     */
    protected function bindFiles(array &$attachments)
    {
        $files = $this->getUploadFileDao()->findByIds(ArrayToolkit::column($attachments, 'fileId'));
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
     * attachment 增加key file.
     *
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

        for ($i = 0; $i < $length; ++$i) {
            $key .= $chars[mt_rand(0, strlen($chars) - 1)];
        }

        return $key;
    }

    /**
     * @return UploadFileDao
     */
    protected function getUploadFileDao()
    {
        return $this->createDao('File:UploadFileDao');
    }

    /**
     * @return UploadFileShareDao
     */
    protected function getUploadFileShareDao()
    {
        return $this->createDao('File:UploadFileShareDao');
    }

    /**
     * @return UploadFileCollectDao
     */
    protected function getUploadFileCollectDao()
    {
        return $this->createDao('File:UploadFileCollectDao');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->biz->service('User:UserService');
    }

    /**
     * @param  $key
     *
     * @throws UploadFileException
     * @throws \Exception
     *
     * @return FileImplementor
     */
    protected function getFileImplementor($key)
    {
        if (!array_key_exists($key, self::$implementor)) {
            $this->createNewException(UploadFileException::IMPLEMENTOR_NOT_ALLOWED());
        }

        return $this->biz->service(self::$implementor[$key]);
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->biz->service('System:LogService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }

    /**
     * @TODO TagService 迁移后再改动
     */
    protected function getTagService()
    {
        return ServiceKernel::instance()->createService('Taxonomy:TagService');
    }

    /**
     * @return UploadFileTagDao
     */
    protected function getUploadFileTagDao()
    {
        return $this->createDao('File:UploadFileTagDao');
    }

    /**
     * @return UploadFileInitDao
     */
    protected function getUploadFileInitDao()
    {
        return $this->createDao('File:UploadFileInitDao');
    }

    /**
     * @return FileUsedDao
     */
    protected function getFileUsedDao()
    {
        return $this->createDao('File:FileUsedDao');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }

    /**
     * @return FireWallFactory
     */
    protected function getFireWallFactory()
    {
        return $this->biz['file_fire_wall_factory'];
    }

    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return \Biz\Course\Service\MaterialService
     */
    protected function getMaterialService()
    {
        return $this->createService('Course:MaterialService');
    }
}
