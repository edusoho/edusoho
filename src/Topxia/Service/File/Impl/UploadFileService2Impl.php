<?php

namespace Topxia\Service\File\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\Common\FileToolkit;
use Topxia\Service\Common\BaseService;
use Topxia\Service\File\UploadFileService2;
use Topxia\Service\CloudPlatform\Client\CloudAPI;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Topxia\Service\Common\ServiceKernel;
    
class UploadFileService2Impl extends BaseService implements UploadFileService2
{
	static $implementor = array(
        'local'=>'File.LocalFileImplementor2',
        'cloud' => 'File.CloudFileImplementor2',
    );

    public function getFile($id)
    {
        $file = $this->getUploadFileDao()->getFile($id);
        if(empty($file)){
            return null;
        }

        return $this->getFileImplementor($file)->getFile($file);
    }

    public function getFileByGlobalId($globalId)
    {
        $file = $this->getUploadFileDao()->getFileByGlobalId($globalId);
        if (empty($file)) {
            return null;
        }

        return $this->getFileImplementor($file)->getFile($file);
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

        $setting = $this->getSettingService()->get('storage');
        $params['storage'] = empty($setting['upload_mode']) ? 'local' : $setting['upload_mode'];

        $implementor = $this->getFileImplementorByStorage($params['storage']);
        $file = $implementor->prepareUpload($params);

        $resumed = $implementor->resumeUpload($params['hash'], array_merge($file, array('bucket' => $params['bucket'])));
        if ($resumed) {
            $this->getUploadFileDao()->updateFile($resumed['outerId'], array(
                'filename' => $file['filename'],
                'targetId' => $file['targetId'],
                'targetType' => $file['targetType'],
            ));
            return $resumed;
        }

        $file = $this->getUploadFileDao()->addFile($file);

        $file['bucket'] = $params['bucket'];
        $file['hash'] = $params['hash'];
        $file['processParams'] = empty($params['processParams']) ? array() : $params['processParams'];
        if (!empty($file['processParams'])) {
            $file['processParams']['callback'] = $params['processCallback'];
        }
        $file['uploadCallback'] = $params['uploadCallback'];

        $params = $implementor->initUpload($file);

        $file = $this->getUploadFileDao()->updateFile($file['id'], array('globalId' => $params['globalId']));

        return $params;
    }

    public function finishedUpload($params)
    {
        $file = $this->getFileByGlobalId($params['globalId']);
        if (empty($file['globalId'])) {
            throw $this->createServiceException("文件不存在(global id: #{$params['globalId']})，完成上传失败！");
        }

        $convertStatus = empty($file['convertParams']) ? 'none' : 'waiting';

    	$file = $this->getUploadFileDao()->updateFile($file['id'], array(
            'status' => 'ok',
            'convertStatus' => $convertStatus,
        ));
    }

    public function setFileProcessed($params)
    {
        try {

            $file = $this->getUploadFileDao()->getFileByGlobalId($params['globalId']);

            $fields = array(
                'convertStatus' => 'success',
            );

            $this->getUploadFileDao()->updateFile($file['id'], $fields);


        } catch (\Exception $e) {
            $msg = $e->getMessage();

            file_put_contents('/tmp/error', $msg);
        }
    }

    public function deleteFiles(array $ids)
    {
        foreach ($ids as $id) {
            $this->getUploadFileDao()->deleteFile($id);
        }
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