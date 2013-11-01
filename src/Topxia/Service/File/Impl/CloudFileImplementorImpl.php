<?php
namespace Topxia\Service\File\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\File\FileImplementor;
use Topxia\Common\FileToolkit;
use Topxia\Service\Util\CloudClientFactory;


class CloudFileImplementorImpl extends BaseService implements FileImplementor
{   

    private  $cloudClient;

	public function getFile($file)
	{
       $file['metas'] = $this->deCodeMetas($file['metas']);
	   $file['path'] = $this->getCloudClient()->getFileUrl($file['hashId'],$file['targetId'],$file['targetType']);
       return $file;
	}

    public function addFile($targetType,$targetId,array $fileInfo=array(),UploadedFile $originalFile=null)
    {

        if (!ArrayToolkit::requireds($fileInfo, array('filename','storage', 'size'))) {
            throw $this->createServiceException('参数缺失，添加用户文件失败!');
        }

        $uploadFile = array();
        $uploadFile['createdUid'] = $this->getCurrentUser()->id;
        $uploadFile['updatedUid'] = $this->getCurrentUser()->id;
        $uploadFile['filename'] = $fileInfo['filename'];
        $path_parts = pathinfo($uploadFile['filename'],);
        $uploadFile['ext'] = $path_parts['extension'];
        $uploadFile['size'] = (int) $fileInfo['size'];
        $uploadFile['targetId'] = $targetId;
        $uploadFile['targetType'] = $targetType;       
        
        if (empty($fileInfo['convertId']) or empty($fileInfo['convertKey'])) {
            $uploadFile['hashId'] = FileToolkit::uniqid($targetType);
            $uploadFile['convertStatus'] = 'none';
        } else {
            $uploadFile['hashId'] = "{$fileInfo['convertId']}:{$fileInfo['convertKey']}";
            $uploadFile['convertStatus'] = 'waiting';
        }
        $uploadFile['storage'] = $fileInfo['storage'];
        $uploadFile['canDownload'] = $fileInfo['canDownload'];
        $uploadFile['type'] = $this->getFileType($uploadFile['mimeType']);
        $uploadFile['updatedTime'] = $uploadFile['createdTime'] = time();


        return $uploadFile; 
    }

    public function convertFile($file,$status,$metas=null,$callback = null)
    {

    }

    public function deleteSubFile($file,$subFileHashId)
    {
        $file = $this->getFile($file);
        if(empty($file['metas'])){
            return;
        } 
        foreach ($file['metas'] as $key => $value) {
            if($subFileHashId==$value['key']){
               $this->getCloudClient()->removeFile($subFileHashId);
               unset($file['metas'][$key]);
               break;
            }
        } 
        $file['metas'] = $this->encodeMetas($file['metas']);
        return $file;             
    }

    public function deleteFile($file)
    {
    	$file = $this->getFile($file);
        if(empty($file['metas'])){
            return;
        }
        foreach ($file['metas'] as $key => $value) {
            $this->getCloudClient()->removeFile($value['key']);
        }
        $this->getCloudClient()->removeFile($file['hashId']);

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

    private function encodeMetas($metas)
    {
        if(empty($metas)) return null;
        return json_encode($metas);
    }

    private function deCodeMetas($metas)
    {
        if(empty($metas)) return null;
        return json_decode($metas);
    }

    private function getCloudClient()
    {
        if($this->cloudClient==null){
            $factory = new CloudClientFactory();
            $this->cloudClient; = $factory->createClient();
        }
        return $this->cloudClient;
    }

}
