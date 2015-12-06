<?php

namespace Topxia\Service\File\Impl;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Topxia\Service\Common\BaseService;
use Topxia\Service\File\FileImplementor;
use Topxia\Common\FileToolkit;

class LocalFileImplementor2Impl extends BaseService implements FileImplementor
{
    public function getFile($file)
    {
        $file['fullpath'] = $this->getFileFullPath($file);
        $file['webpath'] = $this->getFileWebPath($file);

        return $file;
    }

    public function addFile($targetType, $targetId, array $fileInfo = array(), UploadedFile $originalFile = null)
    {
        $errors = FileToolkit::validateFileExtension($originalFile);
        if ($errors) {
            @unlink($originalFile->getRealPath());
            throw $this->createServiceException('该文件格式，不允许上传。');
        }

        $uploadFile = array();

        $uploadFile['storage'] = 'local';
        $uploadFile['targetId'] = $targetId;
        $uploadFile['targetType'] = $targetType;

        $uploadFile['filename'] = $originalFile->getClientOriginalName();

        $uploadFile['ext'] = FileToolkit::getFileExtension($originalFile);
        $uploadFile['size'] = $originalFile->getSize();

        $filename = FileToolkit::generateFilename($uploadFile['ext']);

        $uploadFile['hashId'] = "{$uploadFile['targetType']}/{$uploadFile['targetId']}/{$filename}";

        $uploadFile['convertHash'] = "ch-{$uploadFile['hashId']}";
        $uploadFile['convertStatus'] = 'none';

        $uploadFile['type'] = FileToolkit::getFileTypeByExtension($uploadFile['ext']);

        $uploadFile['isPublic'] = empty($fileInfo['isPublic']) ? 0 : 1;
        $uploadFile['canDownload'] = empty($uploadFile['canDownload']) ? 0 : 1;

        $uploadFile['updatedUserId'] = $uploadFile['createdUserId'] = $this->getCurrentUser()->id;
        $uploadFile['updatedTime'] = $uploadFile['createdTime'] = time();

        $targetPath = $this->getFilePath($targetType, $targetId, $uploadFile['isPublic']);

        $originalFile->move($targetPath, $filename);

        return $uploadFile;
    }

    public function initUpload($file)
    {
        $params = array(
            "extno"  => $file['extno'],
            "bucket" => $file['bucket'],
            "key"    => $file['key'],
            "hash"   => $file['hash'],
            'name'   => $file['name'],
            'size'   => $file['size']
        );

        $api = CloudAPIFactory::create();
        return $api->post('/resources/upload_init', $params);
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

        // 以下参数在cloud模式下弃用，填充随机值
        $keySuffix           = date('Ymdhis').'-'.substr(base_convert(sha1(uniqid(mt_rand(), true)), 16, 36), 0, 16);
        $key                 = "{$params['targetType']}-{$params['targetId']}/{$keySuffix}";
        $file['hashId']      = $key;
        $file['etag']        = $file['hashId'];
        $file['convertHash'] = $file['hashId'];

        return $file;
    }

    public function saveConvertResult($file, array $result = array())
    {
    }

    public function convertFile($file, $status, $result = null, $callback = null)
    {
        throw $this->createServiceException('本地文件暂不支持转换');
    }

    public function deleteFile($file)
    {
        $filename = $this->getFileFullPath($file);
        @unlink($filename);

        return true;
    }

    public function makeUploadParams($params)
    {
        $uploadParams = array();

        $uploadParams['storage'] = 'local';
        $uploadParams['url'] = $params['defaultUploadUrl'];
        $uploadParams['postParams'] = array();
        $uploadParams['postParams']['token'] = $this->getUserService()->makeToken('fileupload', $params['user'], strtotime('+ 2 hours'));

        return $uploadParams;
    }

    public function getMediaInfo($key, $mediaType)
    {
    }

    public function reconvertFile($file, $convertCallback, $pipeline = null)
    {
    }

    protected function getFileFullPath($file)
    {
        if (empty($file['isPublic'])) {
            $baseDirectory = $this->getKernel()->getParameter('topxia.disk.local_directory');
        } else {
            $baseDirectory = $this->getKernel()->getParameter('topxia.upload.public_directory');
        }

        return $baseDirectory.DIRECTORY_SEPARATOR.$file['hashId'];
    }

    protected function getFileWebPath($file)
    {
        if (empty($file['isPublic'])) {
            return '';
        }

        return $this->getKernel()->getParameter('topxia.upload.public_url_path').'/'.$file['hashId'];
    }

    protected function getFilePath($targetType, $targetId, $isPublic)
    {
        if ($isPublic) {
            $baseDirectory = $this->getKernel()->getParameter('topxia.upload.public_directory');
        } else {
            $baseDirectory = $this->getKernel()->getParameter('topxia.disk.local_directory');
        }

        return $baseDirectory.DIRECTORY_SEPARATOR.$targetType.DIRECTORY_SEPARATOR.$targetId;
    }

    protected function getUserService()
    {
        return $this->createService('User.UserService');
    }
}
