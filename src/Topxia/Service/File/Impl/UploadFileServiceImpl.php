<?php

namespace Topxia\Service\File\Impl;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\FileToolkit;
use Topxia\Service\Common\BaseService;
use Topxia\Service\Util\EdusohoCloudClient;
use Topxia\Service\File\UploadFileService;
    
class UploadFileServiceImpl extends BaseService implements UploadFileService
{
	static $IMPEMNTORMAP = array('local'=>'File.LocalFileImplementor','cloud' => 'File.CloudFileImplementor');


    public function getFile($id)
    {
       $file = $this->getUploadFileDao()->getFile($id);
       return $this->getFileImplementorByFile($file)->getFile($file);
    }

    public function getFileByHashId($hashId)
    {
       $file = $this->getUploadFileDao()->getFileByHashId($hashId);
       return $this->getFileImplementorByFile($file)->getFile($file);
    }

    public function findFilesByIds(array $ids)
    {
       return  $this->getUploadFileDao()->findFilesByIds($ids);
    }

    public function searchFiles($conditions, $sort, $start, $limit)
    {
        switch ($sort) {
            case 'latestUpdated':
                $orderBy = array('updatedTime', 'DESC');
                break;
            case 'oldestUpdated':
                $orderBy =  array('updatedTime', 'ASC');
                break; 
            case 'latestCreated':
                $orderBy =  array('createdTime', 'DESC');
                break;
            case 'oldestCreated':
                $orderBy =  array('createdTime', 'ASC');
                break;            
            case 'extAsc':
                $orderBy =  array('ext', 'ASC');
                break;            
            case 'extDesc':
                $orderBy =  array('ext', 'DESC');
                break;
            case 'nameAsc':
                $orderBy =  array('filename', 'ASC');
                break;            
            case 'nameDesc':
                $orderBy =  array('filename', 'DESC');
                break;
            default:
                throw $this->createServiceException('参数sort不正确。');
        }

        return $this->getUploadFileDao()->searchFiles($conditions, $orderBy, $start, $limit);
    }

    public function searchFileCount($conditions)
    {
        return $this->getUploadFileDao()->searchFileCount($conditions);
    }

    public function addFile($targetType,$targetId,array $fileInfo=array(),$implemtor='local',UploadedFile $originalFile=null)    
    {
        $file = $this->getFieImplementor($implemtor)->addFile($targetType,$targetId,$fileInfo,$originalFile);
        return $this->getUploadFileDao()->addFile($file);
    }


    public function renameFile($id, $newFilename)
    {
        $this->getUploadFileDao()->updateFile($id,array('filename'=>$newFilename));
        return $this->getFile($id);
    }

    public function deleteFile($id)
    {
        $file = $this->getFile($id);
        if (empty($file)) {
            throw $this->createServiceException("文件(#{$id})不存在，删除失败");
        }
        $this->getFieImplementor($file)->deleteFile($file);

        return $this->getUploadFileDao()->deleteFile($id);
    }

    public function deleteFiles(array $ids)
    {
        foreach ($ids as $id) {
            $this->deleteFile($id);
        }
    }



    private function getFileType($mimeType)
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

    private function getFileImplementorByFile($file)
    {
        return $this->getFieImplementor($file['storage']);
    }

    private function getUploadFileDao()
    {
        return $this->createDao('File.UploadFileDao');
    }

    private function getUserService()
    {
        return $this->createService('User.UserService');
    }

    private function getFieImplementor($key)
    {
        return $this->createService(self::$IMPEMNTORMAP[$key]);
    }

    private function getLogService()
    {
        return $this->createService('System.LogService');        
    }
}
