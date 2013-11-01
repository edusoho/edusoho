<?php
namespace Topxia\Service\File\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\File\FileImplementor;
use Topxia\Common\FileToolkit;


class CloudFileImplementorImpl extends BaseService implements FileImplementor
{   
	public function getFile($file)
	{
		$file['filename'] = $this->getFileFullName($file);
		return $file;
	}
    public function addFile($targetType,$targetId,array $fileInfo=array(),UploadedFile $originalFile=null)
    {
  
    }
    public function convertFile($file,$status,$metas=null,$callback = null)
    {
    }

    public function deleteSubFile($file,$subFileHashId)
    {
    }

    public function deleteFile($file)
    {
    	$file = $this->getFile($file);
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
