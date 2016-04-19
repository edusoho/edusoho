<?php

namespace Topxia\Service\File\Impl;

use Topxia\Common\FileToolkit;
use Topxia\Service\Common\BaseService;
use Topxia\Service\File\FileImplementor2;

class LocalFileImplementor2Impl extends BaseService implements FileImplementor2
{
    public function getFile($file)
    {
    }

    public function updateFile($globalId, $fields)
    {
    }

    public function findFiles($file, $conditions)
    {
    }

    public function prepareUpload($params)
    {
        $file             = array();
        $file['filename'] = empty($params['fileName']) ? '' : $params['fileName'];

        $pos         = strrpos($file['filename'], '.');
        $file['ext'] = empty($pos) ? '' : substr($file['filename'], $pos + 1);

        $file['fileSize']   = empty($params['fileSize']) ? 0 : $params['fileSize'];
        $file['status']     = 'uploading';
        $file['targetId']   = $params['targetId'];
        $file['targetType'] = $params['targetType'];
        $file['storage']    = 'local';

        $file['type'] = FileToolkit::getFileTypeByExtension($file['ext']);

        $file['updatedUserId'] = empty($params['userId']) ? 0 : $params['userId'];
        $file['updatedTime']   = time();
        $file['createdUserId'] = $file['updatedUserId'];
        $file['createdTime']   = $file['updatedTime'];

        $filename       = FileToolkit::generateFilename($file['ext']);
        $file['hashId'] = "{$file['targetType']}/{$file['targetId']}/{$filename}";

        $file['convertHash']   = "ch-{$file['hashId']}";
        $file['convertStatus'] = 'none';

        return $file;
    }

    public function moveFile($targetType, $targetId, $originalFile = null, $data)
    {
        $errors = FileToolkit::validateFileExtension($originalFile);

        if ($errors) {
            @unlink($originalFile->getRealPath());
            throw $this->createServiceException("该文件格式，不允许上传。");
        }

        $targetPath = $this->getFilePath($targetType, $targetId);

        $filename = str_replace("{$targetType}/{$targetId}/", "", $data['hashId']);
        $originalFile->move($targetPath, $filename);
    }

    protected function getFilePath($targetType, $targetId)
    {
        $baseDirectory = $this->getKernel()->getParameter('topxia.disk.local_directory');
        return $baseDirectory.DIRECTORY_SEPARATOR.$targetType.DIRECTORY_SEPARATOR.$targetId;
    }

    public function finishedUpload($file, $params)
    {
        return array_merge(array('success' => true, 'convertStatus' => 'success'), $params);
    }

    public function resumeUpload($hash, $params)
    {
    }

    public function getDownloadFile($file)
    {
        return $file;
    }

    public function deleteFile($file)
    {
        $filename = $this->getFileFullPath($file);
        @unlink($filename);
        return array('success' => true);
    }

    public function search($conditions)
    {
    }

    public function synData($conditions)
    {
    }

    public function get($globalId)
    {
    }

    public function initUpload($params)
    {
        $uploadParams = array();

        $uploadParams['uploadMode']          = 'local';
        $uploadParams['url']                 = "/uploadfile/upload?targetId={$params['targetId']}&targetType={$params['targetType']}";
        $uploadParams['postParams']          = array();
        $uploadParams['postParams']['token'] = $this->getUserService()->makeToken('fileupload', $params['userId'], strtotime('+ 2 hours'), $params);

        return $uploadParams;
    }

    protected function getFileFullPath($file)
    {
        $baseDirectory = $this->getKernel()->getParameter('topxia.disk.local_directory');
        return $baseDirectory.DIRECTORY_SEPARATOR.$file['hashId'];
    }

    protected function getUserService()
    {
        return $this->createService('User.UserService');
    }
}
