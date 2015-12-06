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

        return $this->getFileImplementor($file)->getFile($file);
    }

    public function getThinFile($id)
    {
        $file = $this->getUploadFileDao()->getFile($id);

        if (empty($file)) {
            return null;
        }

        return ArrayToolkit::parts($file, array('id', 'globalId', 'targetId', 'targetType', 'filename', 'ext', 'fileSize', 'length', 'status', 'type', 'storage', 'createdUserId', 'createdTime'));
    }

    public function getFileByGlobalId($globalId)
    {
        $file = $this->getUploadFileDao()->getFileByGlobalId($globalId);

        if (empty($file)) {
            return null;
        }

        return $this->getFileImplementor($file)->getFile($file);
    }

    public function findFilesByIds(array $ids)
    {
        $files = $this->getUploadFileDao()->findFilesByIds($ids);

        if (empty($files)) {
            return array();
        }

        return $this->mergeImplFiles($files);
    }

    public function searchFiles($conditions, $orderBy, $start, $limit)
    {
        $conditions = $this->_prepareSearchConditions($conditions);
        $files      = $this->getUploadFileDao()->searchFiles($conditions, $orderBy, $start, $limit);

        if (empty($files)) {
            return array();
        }

        return $this->mergeImplFiles($files);
    }

    public function searchFilesCount($conditions)
    {
        $conditions = $this->_prepareSearchConditions($conditions);
        return $this->getUploadFileDao()->searchFileCount($conditions);
    }

    public function getDownloadFile($id)
    {
        $file = $this->getUploadFileDao()->getFile($id);

        if (empty($file)) {
            return array('error' => 'not_found', 'message' => '文件不存在，不能下载！');
        }

        return $this->getFileImplementor($file)->getDownloadFile($file);
    }

    public function initUpload($params)
    {
        $user = $this->getCurrentUser();

        if (empty($user)) {
            throw $this->createServiceException("用户未登录，上传初始化失败！");
        }

        if (!ArrayToolkit::requireds($params, array('targetId', 'targetType', 'bucket', 'hash'))) {
            throw $this->createServiceException("参数缺失，上传初始化失败！");
        }

        $setting           = $this->getSettingService()->get('storage');
        $params['storage'] = empty($setting['upload_mode']) ? 'local' : $setting['upload_mode'];
        $implementor       = $this->getFileImplementorByStorage($params['storage']);
        $file              = $implementor->prepareUpload($params);

        if (isset($params['outerId'])) {
            $outterFile       = $this->getUploadFileDao()->getFile($params['outerId']);
            $initUploadParams = array(
                'extno'  => $outterFile['id'],
                'bucket' => 'private',
                'size'   => $params['fileSize'],
                'hash'   => $params['hash'],
                'name'   => $params['fileName']
            );
            $resumedResult = $implementor->resumeUpload($outterFile['globalId'], $initUploadParams);

            if ($resumedResult['resumed'] == 'ok' && $outterFile) {
                $file = $this->getUploadFileDao()->updateFile($resumedResult['extno'], array(
                    'filename'   => $file['filename'],
                    'targetId'   => $file['targetId'],
                    'targetType' => $file['targetType']
                ));

                $result                   = array();
                $result['globalId']       = $file['globalId'];
                $result['outerId']        = $file['id'];
                $result['uploadMode']     = $resumedResult['uploadMode'];
                $result['uploadUrl']      = 'http://upload.edusoho.net';
                $result['uploadProxyUrl'] = '';
                $result['uploadToken']    = $resumedResult['uploadToken'];
                $result['resumed']        = 'ok';
                return $result;
            }
        }

        $file = $this->getUploadFileDao()->addFile($file);

        $initUploadParams = array(
            'extno'  => $file['id'],
            'bucket' => 'private',
            'key'    => $file['hashId'],
            'hash'   => $params['hash'],
            'name'   => $params['fileName'],
            'size'   => $params['fileSize']
        );
        $params = $implementor->initUpload($initUploadParams);

        $file = $this->getUploadFileDao()->updateFile($file['id'], array('globalId' => $params['no']));

        $result                   = array();
        $result['globalId']       = $file['globalId'];
        $result['outerId']        = $file['id'];
        $result['uploadMode']     = $params['uploadMode'];
        $result['uploadUrl']      = 'http://upload.edusoho.net';
        $result['uploadProxyUrl'] = '';
        $result['uploadToken']    = $params['uploadToken'];

        return $result;
    }

    public function finishedUpload($params)
    {
        $file = $this->getFileByGlobalId($params['globalId']);

        if (empty($file['globalId'])) {
            throw $this->createServiceException("文件不存在(global id: #{$params['globalId']})，完成上传失败！");
        }

        $convertStatus = empty($file['convertParams']) ? 'none' : 'waiting';

        $file = $this->getUploadFileDao()->updateFile($file['id'], array(
            'status'        => 'ok',
            'convertStatus' => $convertStatus
        ));
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

    public function deleteFiles(array $ids)
    {
        foreach ($ids as $id) {
            $this->getUploadFileDao()->deleteFile($id);
        }
    }

    public function increaseFileUsedCount($id)
    {
        $this->getUploadFileDao()->waveFileUsedCount($id, +1);
    }

    public function decreaseFileUsedCount($id)
    {
        $this->getUploadFileDao()->waveFileUsedCount($id, -1);
    }

    protected function _prepareSearchConditions($conditions)
    {
        $conditions['createdUserIds'] = empty($conditions['createdUserIds']) ? array() : $conditions['createdUserIds'];

        if (isset($conditions['source']) && ($conditions['source'] == 'shared') && !empty($conditions['currentUserId'])) {
            $sharedUsers                  = $this->getUploadFileShareDao()->findMySharingContacts($conditions['currentUserId']);
            $sharedUserIds                = ArrayToolkit::column($sharedUsers, 'sourceUserId');
            $conditions['createdUserIds'] = array_merge($conditions['createdUserIds'], $sharedUserIds);
        }

        if (!empty($conditions['currentUserId'])) {
            $conditions['createdUserIds'] = array_merge($conditions['createdUserIds'], array($conditions['currentUserId']));
            unset($conditions['currentUserId']);
        }

        return $conditions;
    }

    protected function mergeImplFiles($files)
    {
        $groupedFiles = array();

        foreach ($files as $file) {
            $name                  = $this->getFileImplementorName($file);
            $groupedFiles[$name][] = $file;
        }

        $implFiles = array();

        foreach ($groupedFiles as $name => $files) {
            $implFiles = array_merge($implFiles, $this->createFileImplementor($name)->findFiles($files));
        }

        return $implFiles;
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

    protected function getUploadFileDao()
    {
        return $this->createDao('File.UploadFileDao');
    }
}
