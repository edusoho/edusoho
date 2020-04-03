<?php

namespace Biz\File\Service\Impl;

use Biz\BaseService;
use Biz\File\Dao\UploadFileDao;
use Biz\File\Service\FileImplementor;
use AppBundle\Common\FileToolkit;
use Biz\File\UploadFileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Biz\User\Service\UserService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class LocalFileImplementorImpl extends BaseService implements FileImplementor
{
    public function getFile($file)
    {
        $file['fullpath'] = $this->getFileFullPath($file);
        $file['webpath'] = '';

        return $file;
    }

    public function getFullFile($file)
    {
        return $this->getFile($file);
    }

    //@todo 目前没用到，如果用到，逻辑要改动
    public function addFile($targetType, $targetId, array $fileInfo = array(), UploadedFile $originalFile = null)
    {
        $errors = FileToolkit::validateFileExtension($originalFile);

        if ($errors) {
            @unlink($originalFile->getRealPath());
            $this->createNewException(UploadFileException::EXTENSION_NOT_ALLOWED());
        }

        $uploadFile = array();

        $uploadFile['filename'] = $originalFile->getClientOriginalName();

        $uploadFile['ext'] = FileToolkit::getFileExtension($originalFile);
        $uploadFile['fileSize'] = $originalFile->getSize();

        $filename = FileToolkit::generateFilename($uploadFile['ext']);

        $uploadFile['hashId'] = "{$targetType}/{$targetId}/{$filename}";

        $uploadFile['convertHash'] = "ch-{$uploadFile['hashId']}";
        $uploadFile['convertStatus'] = 'none';

        $uploadFile['type'] = FileToolkit::getFileTypeByExtension($uploadFile['ext']);

        $uploadFile['isPublic'] = empty($fileInfo['isPublic']) ? 0 : 1;
        $uploadFile['canDownload'] = empty($uploadFile['canDownload']) ? 0 : 1;

        $uploadFile['updatedUserId'] = $uploadFile['createdUserId'] = $this->getCurrentUser()->id;
        $uploadFile['updatedTime'] = $uploadFile['createdTime'] = time();

        $targetPath = $this->getFilePath($targetType, $targetId);

        $originalFile->move($targetPath, $filename);

        return $uploadFile;
    }

    public function saveConvertResult($file, array $result = array())
    {
    }

    public function convertFile($file, $status, $result = null, $callback = null)
    {
        $this->createNewException(UploadFileException::LOCAL_CONVERT_NOT_SUPPORT());
    }

    public function updateFile($file, $fields)
    {
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

    public function reconvert($globalId, $options)
    {
    }

    public function getUploadAuth($params)
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
        $file = array();
        $file['filename'] = empty($params['fileName']) ? '' : $params['fileName'];

        $pos = strrpos($file['filename'], '.');
        $file['ext'] = empty($pos) ? '' : substr($file['filename'], $pos + 1);

        $file['fileSize'] = empty($params['fileSize']) ? 0 : $params['fileSize'];
        $file['status'] = 'uploading';
        $file['targetId'] = $params['targetId'];
        $file['targetType'] = $params['targetType'];
        $file['storage'] = 'local';

        $file['type'] = FileToolkit::getFileTypeByExtension($file['ext']);

        $file['updatedUserId'] = empty($params['userId']) ? 0 : $params['userId'];
        $file['updatedTime'] = time();
        $file['createdUserId'] = $file['updatedUserId'];
        $file['createdTime'] = $file['updatedTime'];

        $filename = FileToolkit::generateFilename($file['ext']);
        $file['hashId'] = "{$file['targetType']}/{$file['targetId']}/{$filename}";

        $file['convertHash'] = "ch-{$file['hashId']}";
        $file['convertStatus'] = 'none';

        return $file;
    }

    public function moveFile($targetType, $targetId, UploadedFile $originalFile = null, $data = array())
    {
        $errors = FileToolkit::validateFileExtension($originalFile);

        if ($errors) {
            @unlink($originalFile->getRealPath());
            $this->createNewException(UploadFileException::EXTENSION_NOT_ALLOWED());
        }

        $targetPath = $this->getFilePath($targetType, $targetId);

        $filename = str_replace("{$targetType}/{$targetId}/", '', $data['hashId']);

        return $originalFile->move($targetPath, $filename);
    }

    public function reconvertOldFile($file, $convertCallback, $pipeline = null)
    {
        return null;
    }

    public function finishedUpload($file, $params)
    {
        return array_merge(array('success' => true, 'convertStatus' => 'success'), $params);
    }

    public function resumeUpload($hash, $params)
    {
    }

    public function getDownloadFile($file, $ssl = false)
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

        return $this->getUploadFileDao()->search($conditions, array('createdTime' => 'DESC'), $start, $limit);
    }

    public function getFileByGlobalId($globalId)
    {
    }

    public function initUpload($params)
    {
        $uploadParams = array();

        $uploadParams['uploadMode'] = 'local';
        $uploadParams['url'] = $this->getUploadUrl($params);
        $uploadParams['postParams'] = array();
        $uploadParams['postParams']['token'] = $this->getUserService()->makeToken(
            'fileupload',
            $params['userId'],
            strtotime('+ 2 hours'),
            $params
        );

        return $uploadParams;
    }

    protected function getUploadUrl($params)
    {
        global $kernel;

        $url = $kernel->getContainer()->get('router')->generate('uploadfile_upload', $params, UrlGeneratorInterface::ABSOLUTE_URL);

        return $url;
    }

    protected function getFileFullPath($file)
    {
        $baseDirectory = $this->biz['topxia.disk.local_directory'];

        return $baseDirectory.DIRECTORY_SEPARATOR.$file['hashId'];
    }

    protected function getFileWebPath($file)
    {
        if (!$file['isPublic']) {
            return '';
        }

        return $this->biz['topxia.upload.public_url_path'].DIRECTORY_SEPARATOR.$file['hashId'];
    }

    protected function getFilePath($targetType, $targetId)
    {
        if (preg_match('/\/|\\\\/i', $targetType.$targetId, $matches)) {
            $this->createNewException(UploadFileException::ARGUMENTS_INVALID());
        }

        $baseDirectory = $this->biz['topxia.disk.local_directory'];

        return $baseDirectory.DIRECTORY_SEPARATOR.$targetType.DIRECTORY_SEPARATOR.$targetId;
    }

    /**
     * only support for cloud file.
     *
     * @param $globalId
     *
     * @return array
     */
    public function download($globalId)
    {
        return array();
    }

    /**
     * only support for cloud file.
     *
     * @param $globalId
     *
     * @return array
     */
    public function getDefaultHumbnails($globalId)
    {
        return array();
    }

    /**
     * only support for cloud file.
     *
     * @param $globalId
     * @param $options
     *
     * @return array
     */
    public function getThumbnail($globalId, $options)
    {
        return array();
    }

    /**
     * only support for cloud file.
     *
     * @param $options
     *
     * @return array
     */
    public function getStatistics($options)
    {
        return array();
    }

    /**
     * @param $globalId
     * @param bool $ssl
     *
     * @deprecated only support for cloud file use CloudFileImplementorImpl
     *
     * @return array
     */
    public function player($globalId, $ssl = false)
    {
        return array();
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->biz->service('User:UserService');
    }

    /**
     * @return UploadFileDao
     */
    protected function getUploadFileDao()
    {
        return $this->createDao('File:UploadFileDao');
    }
}
