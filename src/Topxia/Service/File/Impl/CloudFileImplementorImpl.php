<?php
namespace Topxia\Service\File\Impl;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Topxia\Common\FileToolkit;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\BaseService;
use Topxia\Service\File\FileImplementor;
use Topxia\Service\Util\CloudClientFactory;


class CloudFileImplementorImpl extends BaseService implements FileImplementor
{   

    private  $cloudClient;

	public function getFile($file)
	{
    //    $file['metas'] = $this->decodeMetas($file['metas']);
	   // $file['path'] = $this->getCloudClient()->getFileUrl($file['hashId'],$file['targetId'],$file['targetType']);
       return $file;
	}

    public function addFile($targetType, $targetId, array $fileInfo=array(), UploadedFile $originalFile=null)
    {
        if (!ArrayToolkit::requireds($fileInfo, array('filename','key', 'size'))) {
            throw $this->createServiceException('参数缺失，添加用户文件失败!');
        }

        $uploadFile = array();
        $uploadFile['targetId'] = $targetId;
        $uploadFile['targetType'] = $targetType; 
        $uploadFile['hashId'] = $fileInfo['key'];
        $uploadFile['filename'] = $fileInfo['filename'];
        $uploadFile['ext'] = pathinfo($uploadFile['filename'], PATHINFO_EXTENSION);
        $uploadFile['size'] = (int) $fileInfo['size'];

        $uploadFile['metas'] = $this->encodeMetas(empty($fileInfo['metas']) ? array() : $fileInfo['metas']);    

        if (empty($fileInfo['convertId']) or empty($fileInfo['convertKey'])) {
            $uploadFile['convertHash'] = "ch-{$uploadFile['hashId']}";
            $uploadFile['convertStatus'] = 'none';
        } else {
            $uploadFile['convertHash'] = "{$fileInfo['convertId']}:{$fileInfo['convertKey']}";
            $uploadFile['convertStatus'] = 'waiting';
        }

        $uploadFile['type'] = FileToolkit::getFileTypeByMimeType($fileInfo['mimeType']);
        $uploadFile['canDownload'] = empty($uploadFile['canDownload']) ? 0 : 1;
        $uploadFile['storage'] = 'cloud';
        $uploadFile['createdUserId'] = $this->getCurrentUser()->id;
        $uploadFile['updatedUserId'] = $uploadFile['createdUserId'];
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

    private function encodeMetas($metas)
    {
        if(empty($metas) or !is_array($metas)) {
            $metas = array();
        }
        return json_encode($metas);
    }

    private function decodeMetas($metas)
    {
        if (empty($metas)) {
            return array();
        }
        return json_decode($metas, true);
    }

    private function getCloudClient()
    {
        if(empty($this->cloudClient)) {
            $factory = new CloudClientFactory();
            $this->cloudClient = $factory->createClient();
        }
        return $this->cloudClient;
    }

}
