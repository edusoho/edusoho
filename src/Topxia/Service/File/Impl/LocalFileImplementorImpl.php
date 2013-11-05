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
		$file['path'] = $this->getFileFullName($file);
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
        
        $uploadFile['hashId'] = FileToolkit::generateFilename($uploadFile['ext']);

        $uploadFile['convertHash'] = "ch-{$uploadFile['hashId']}";
        $uploadFile['convertStatus'] = 'none';

        $uploadFile['type'] = FileToolkit::getFileTypeByMimeType($originalFile->getMimeType());

        $uploadFile['canDownload'] = empty($uploadFile['canDownload']) ? 0 : 1;

        $uploadFile['updatedUserId'] = $uploadFile['createdUserId'] = $this->getCurrentUser()->id;
        $uploadFile['updatedTime'] = $uploadFile['createdTime'] = time();

        $targetPath = $this->getFilePath($targetType, $targetId);

        $originalFile->move($targetPath, $uploadFile['hashId']);

        return $uploadFile;
    }
    public function convertFile($file,$status,$metas=null,$callback = null)
    {
    	throw $this->createServiceException('本地文件暂不支持转换');
    }

    public function deleteSubFile($file,$subFileHashId)
    {
    	throw $this->createServiceException('无文件可删除');
    }

    public function deleteFile($file)
    {
    	$filename = $this->getFileFullName($file);
    	@unlink($filename);
    }

    private function getFileFullName($file)
    {
        $path= $this->getFilePath($file['targetType'],$file['targetId']);
        return $path . DIRECTORY_SEPARATOR . $file['hashId'];
    }

    private function getFilePath($targetType, $targetId)
    {
        return $this->getKernel()->getParameter('topxia.disk.local_directory') 
            . DIRECTORY_SEPARATOR. $targetType 
            . DIRECTORY_SEPARATOR . $targetId;
    }

}
