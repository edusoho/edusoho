<?php
namespace Topxia\Service\File\Impl;

use Topxia\Common\FileToolkit;
use Topxia\Service\Common\BaseService;
use Topxia\Service\File\FileImplementor;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class LocalFileImplementorImpl extends BaseService implements FileImplementor
{
    public function getFile($file)
    {
        $file['fullpath'] = $this->getFileFullPath($file);
        $file['webpath']  = '';
        return $file;
    }

    public function getFullFile($file) {
        return $this->getFile($file);
    }

    public function addFile($targetType, $targetId, array $fileInfo = array(), UploadedFile $originalFile = null)
    {
        $errors = FileToolkit::validateFileExtension($originalFile);

        if ($errors) {
            @unlink($originalFile->getRealPath());
            throw $this->createServiceException("该文件格式，不允许上传。");
        }

        $uploadFile = array();

        $uploadFile['filename'] = $originalFile->getClientOriginalName();

        $uploadFile['ext']      = FileToolkit::getFileExtension($originalFile);
        $uploadFile['fileSize'] = $originalFile->getSize();

        $filename = FileToolkit::generateFilename($uploadFile['ext']);

        $uploadFile['hashId'] = "{$uploadFile['targetType']}/{$uploadFile['targetId']}/{$filename}";

        $uploadFile['convertHash']   = "ch-{$uploadFile['hashId']}";
        $uploadFile['convertStatus'] = 'none';

        $uploadFile['type'] = FileToolkit::getFileTypeByExtension($uploadFile['ext']);

        $uploadFile['isPublic']    = empty($fileInfo['isPublic']) ? 0 : 1;
        $uploadFile['canDownload'] = empty($uploadFile['canDownload']) ? 0 : 1;

        $uploadFile['updatedUserId'] = $uploadFile['createdUserId'] = $this->getCurrentUser()->id;
        $uploadFile['updatedTime']   = $uploadFile['createdTime']   = time();

        $targetPath = $this->getFilePath($targetType, $targetId, $uploadFile['isPublic']);

        $originalFile->move($targetPath, $filename);

        return $uploadFile;
    }

    public function saveConvertResult($file, array $result = array())
    {
    }

    public function convertFile($file, $status, $result = null, $callback = null)
    {
        throw $this->createServiceException('本地文件暂不支持转换');
    }

    public function updateFile($file, $fields)
    {
    }

    public function makeUploadParams($params)
    {
        $uploadParams = array();

        $uploadParams['storage']             = 'local';
        $uploadParams['url']                 = $params['defaultUploadUrl'];
        $uploadParams['postParams']          = array();
        $uploadParams['postParams']['token'] = $this->getUserService()->makeToken('fileupload', $params['user'], strtotime('+ 2 hours'));

        return $uploadParams;
    }

    public function getMediaInfo($key, $mediaType)
    {
    }

    public function reconvertFile($file, $convertCallback, $pipeline = null)
    {
    }

    //LocalFileImplementorImpl2

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

        $targetPath = $this->getFilePath($targetType, $targetId, 0);

        $filename = str_replace("{$targetType}/{$targetId}/", "", $data['hashId']);
        $originalFile->move($targetPath, $filename);
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
        $start = $conditions['start'];
        $limit = $conditions['limit'];
        unset($conditions['start']);
        unset($conditions['limit']);

        $files = $this->getUploadFileDao()->searchFiles($conditions, array('createdTime','DESC'), $start, $limit);

        return $file;
    }

    public function synData($conditions)
    {
    }

    public function getFileByGlobalId($globalId)
    {
    }

    public function initUpload($params)
    {
        $uploadParams = array();

        $uploadParams['uploadMode']          = 'local';
        $uploadParams['url']                 = $this->getUploadUrl($params);
        $uploadParams['postParams']          = array();
        $uploadParams['postParams']['token'] = $this->getUserService()->makeToken('fileupload', $params['userId'], strtotime('+ 2 hours'), $params);

        return $uploadParams;
    }

    protected function getUploadUrl($params)
    {
        global $kernel;

        $url = $kernel->getContainer()->get('router')->generate('uploadfile_upload', $params, true);

        return $url;
    }

    protected function getFileFullPath($file)
    {
        $baseDirectory = $this->getKernel()->getParameter('topxia.disk.local_directory');
        return $baseDirectory.DIRECTORY_SEPARATOR.$file['hashId'];
    }

    protected function getFileWebPath($file)
    {
        if (!$file['isPublic']) {
            return '';
        }

        return $this->getKernel()->getParameter('topxia.upload.public_url_path').'/'.$file['hashId'];
    }

    protected function getFilePath($targetType, $targetId, $isPublic)
    {
        $baseDirectory = $this->getKernel()->getParameter('topxia.disk.local_directory');
        return $baseDirectory.DIRECTORY_SEPARATOR.$targetType.DIRECTORY_SEPARATOR.$targetId;
    }

    protected function getUserService()
    {
        return $this->createService('User.UserService');
    }

    protected function getUploadFileDao()
    {
        return $this->createDao('File.UploadFileDao');
    }
}
