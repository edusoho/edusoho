<?php
namespace Topxia\Service\File\Impl;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Topxia\Service\Common\BaseService;
use Topxia\Service\File\FileImplementor;
use Topxia\Common\FileToolkit;


class LocalFileImplementorImpl extends BaseService implements FileImplementor
{   
	public function getFile($file)
	{
		$file['filename'] = $this->getFileFullName($file);
		return $file;
	}
    public function addFile($targetType,$targetId,array $fileInfo=array(),UploadedFile $originalFile=null)
    {
        $errors = FileToolkit::validateFileExtension($originalFile);
        if ($errors) {
            @unlink($originalFile->getRealPath());
            throw $this->createServiceException("该文件格式，不允许上传。");
        }

        $targetPath = $this->getFilePath($targetType,$targetId);

        $uploadFile = array();
        $uploadFile['ext'] =  FileToolkit::getFileExtension($originalFile);;
        $uploadFile['hashId'] = FileToolkit::uniqid($targetType);
        $uploadFile['createdUid'] = $this->getCurrentUser()->id;
        $uploadFile['updatedUid'] = $this->getCurrentUser()->id;
        $uploadFile['filename'] = $originalFile->getClientOriginalName();
        $uploadFile['size'] = $originalFile->getSize();
        $uploadFile['storage'] = 'local';
        $uploadFile['targetId'] = $targetId;
        $uploadFile['targetType'] = $targetType;
        $uploadFile['convertStatus'] = 'none';
        $uploadFile['type'] = $this->getFileType($originalFile->getMimeType());
        $uploadFile['updatedTime'] = $uploadFile['createdTime'] = time();
        if(!empty($fileInfo) && isset($fileInfo['canDownload'])){
        	$uploadFile['canDownload'] = $fileInfo['canDownload'];
        }else{
        	$uploadFile['canDownload'] = false;
        }
        $filename = "{$uploadFile['hashId']}.{$uploadFile['ext']}";
        $originalFile->move($targetPath,$filename);

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
        $diskDirectory= $this->getFilePath($file['targetType'],$file['targetId']);
        $filename .= "{$file['hashId']}.{$file['ext']}";
        return $diskDirectory.$filename; 
    }

    private function getFilePath($targetType,$targetId)
    {
        $diskDirectory = $this->getKernel()->getParameter('topxia.disk.local_directory');
        $subDir = DIRECTORY_SEPARATOR.$file['targetType'].DIRECTORY_SEPARATOR;
        $subDir .= "{$file['targetType']}-{$file['targetId']}".DIRECTORY_SEPARATOR;
        return $diskDirectory.$subDir;    	
    }

    protected function getFileType($mimeType)
    {
    	if (strpos($mimeType, 'video') === 0) {
    		return 'video';
    	} elseif (strpos($mimeType, 'audio') === 0) {
    		return 'audio';
    	} elseif (strpos($mimeType, 'image') === 0) {
    		return 'image';
    	} elseif (strpos($mimeType, 'application/vnd.ms-') === 0 
            or strpos($mimeType, 'application/vnd.openxmlformats-officedocument') === 0
            or strpos($mimeType, 'application/pdf') === 0) {
    		return 'document';
    	}

    	return 'other';
    }

}
