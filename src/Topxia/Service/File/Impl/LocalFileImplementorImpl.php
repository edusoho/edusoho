<?php
namespace Topxia\Service\File\Impl;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Topxia\Service\Common\BaseService;
use Topxia\Service\File\FileImplementor;
use Topxia\Common\FileToolkit;
use Topxia\Common\ArrayToolkit;


class LocalFileImplementorImpl extends BaseService implements FileImplementor
{   
	public function getFile($file)
	{
		$file['fullpath'] = $this->getFileFullPath($file);
        $file['webpath'] = $this->getFileWebPath($file);
		return $file;
	}
    public function addFile($targetType, $targetId, array $fileInfo=array(), UploadedFile $originalFile=null)
    {
        $errors = FileToolkit::validateFileExtension($originalFile);
        if ($errors) {
            @unlink($originalFile->getRealPath());
            throw $this->createServiceException("该文件格式，不允许上传。");
        }

        $uploadFile = array();

        $uploadFile['storage'] = 'local';
        $uploadFile['targetId'] = $targetId;
        $uploadFile['targetType'] = $targetType;

        $uploadFile['filename'] = $originalFile->getClientOriginalName();

        $uploadFile['ext'] =  FileToolkit::getFileExtension($originalFile);;
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

    public function saveConvertResult($file, array $result = array())
    {

    }

    public function convertFile($file, $status, $result=null, $callback = null)
    {
    	throw $this->createServiceException('本地文件暂不支持转换');
    }

    public function deleteFile($file, $deleteSubFile = true)
    {
    	$filename = $this->getFileFullPath($file);
    	@unlink($filename);
    }

    public function makeUploadParams($params)
    {
        $uploadParams = array();

        $uploadParams['storage'] = 'local';
        $uploadParams['url'] = $params['defaultUploadUrl'];
        $uploadParams['postParams'] = array();
        $uploadParams['postParams']['token'] =  $this->getUserService()->makeToken('fileupload', $params['user'], strtotime('+ 2 hours'));

        return $uploadParams;
    }

    public function getMediaInfo($key, $mediaType)
    {

    }

    public function reconvertFile($file, $convertCallback, $pipeline = null)
    {
        
    }

    private function getFileFullPath($file)
    {
        if (empty($file['isPublic'])) {
            $baseDirectory =  $this->getKernel()->getParameter('topxia.disk.local_directory');
        } else {
            $baseDirectory = $this->getKernel()->getParameter('topxia.upload.public_directory');
        }

        return $baseDirectory . DIRECTORY_SEPARATOR . $file['hashId'];
    }

    private function getFileWebPath($file)
    {
        if (empty($file['isPublic'])) {
            return '';
        }

        return $this->getKernel()->getParameter('topxia.upload.public_url_path') . '/' . $file['hashId'];
    }

    private function getFilePath($targetType, $targetId, $isPublic)
    {
        if ($isPublic) {
            $baseDirectory = $this->getKernel()->getParameter('topxia.upload.public_directory');
        } else {
            $baseDirectory = $this->getKernel()->getParameter('topxia.disk.local_directory');
        }
        return $baseDirectory . DIRECTORY_SEPARATOR. $targetType . DIRECTORY_SEPARATOR . $targetId;
    }

    private function getUserService()
    {
        return $this->createService('User.UserService');
    }
}
