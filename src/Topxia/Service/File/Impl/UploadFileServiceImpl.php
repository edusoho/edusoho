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
       if(empty($file)) return null;
       return $this->getFileImplementorByFile($file)->getFile($file);
    }

    public function getFileByHashId($hashId)
    {
       $file = $this->getUploadFileDao()->getFileByHashId($hashId);
       if(empty($file)) return null;
       return $this->getFileImplementorByFile($file)->getFile($file);
    }

    public function getFileByConvertHash($hash)
    {
        return $this->getUploadFileDao()->getFileByConvertHash($hash);
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
        $this->getFileImplementorByFile($file)->deleteFile($file);

        return $this->getUploadFileDao()->deleteFile($id);
    }

    public function deleteFiles(array $ids)
    {
        foreach ($ids as $id) {
            $this->deleteFile($id);
        }
    }

    public function convertFile($id, $status, array $result = array())
    {
        $statuses = array('none', 'waiting', 'doing', 'success', 'error');
        if (!in_array($status, $statuses)) {
            throw $this->createServiceException('状态不正确，变更文件转换状态失败！');
        }

        $file = $this->getFile($id);
        if (empty($file)) {
            throw $this->createServiceException("文件(#{$id})不存在，转换失败");
        }

        $file = $this->getFileImplementorByFile($file)->convertFile($file, $status, $result);

        $this->getUploadFileDao()->updateFile($id, array(
            'convertStatus' => $file['convertStatus'],
            'metas2' => $file['metas2']
        ));

        return $this->getFile($id);
    }

    public function setFileConverting($id, $convertHash)
    {
        $file = $this->getFile($id);
        if (empty($file)) {
            throw $this->createServiceException('file not exist.');
        }

        $status = $file['convertStatus'] == 'success' ? 'success' : 'waiting';

        $fields = array(
            'convertStatus' => $status,
            'convertHash' => $convertHash,
        );

        $this->getUploadFileDao()->updateFile($id, $fields);

        return $this->getFile($id);
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
