<?php

namespace Topxia\Service\File\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Util\EdusohoCloudClient;
use Topxia\Common\FileToolkit;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Topxia\Service\File\UploadFileService;
    
class UploadFileServiceImpl extends BaseDao implements UploadFileService
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
       // $result = array();
       // foreach ($files as $file) {
       //     $result[] = $this->getFileImplementorByFile($file)->getFile($file);
       // }

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

        $conditions = $this->prepareSearchConditions($conditions);

        return $this->getUploadFileDao()->searchFiles($conditions, $orderBy, $start, $limit);
    }

    private function prepareSearchConditions($conditions)
    {
        $conditions = array_filter($conditions);

        if (isset($conditions['nickname'])) {
            $user = $this->getUserService()->getUserByNickname($conditions['nickname']);
            if(empty($user)){
                 $conditions['userId'] = 0;
            } else {
                $conditions['userId'] = $user['id'];
            }
            unset($conditions['nickname']);
        }
        return $conditions;
    }

    public function searchFileCount($conditions)
    {
        $conditions = $this->prepareSearchConditions($conditions);
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

    public function setFileFormats($id, array $items)
    {
        $cmds = EdusohoCloudClient::getVideoConvertCommands();

        $formats = array();
        foreach ($items as $item) {
            $type = empty($cmds[$item['cmd']]) ? null : $cmds[$item['cmd']];
            if (empty($type)) {
                continue;
            }

            if ($item['code'] != 0) {
                continue;
            }

            if (empty($item['key'])) {
                continue;
            }

            $formats[$type] = array('type' => $type, 'cmd' => $item['cmd'], 'key' => $item['key']);
        }

        if (empty($formats)) {
            $fields = array('convertStatus' => 'error', 'formats' => $formats);
        } else {
            $fields = array('convertStatus' => 'success', 'formats' => $formats);
        }

        return DiskFileSerialize::unserialize(
            $this->getUploadFileDao()->updateFile($id, DiskFileSerialize::serialize($fields))
        );
    }

    public function changeFileConvertStatus($id, $status)
    {
        $statuses = array('none', 'waiting', 'doing', 'success', 'error');
        if (!in_array($status, $statuses)) {
            throw $this->createServiceException('状态不正确，变更文件转换状态失败！');
        }

        $this->getUploadFileDao()->updateFile($id, array('convertStatus' => $status));
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


class UserLocalDisk
{
    private $diskDirectory;

    private $user;

    public function __construct($user, $diskDirectory)
    {
        if (!is_dir($diskDirectory)) {
            throw new \RuntimeException("{$diskDirectory}目录不存在，请先创建。");
        }

        $this->diskDirectory = $diskDirectory;
        $this->user = $user;
    }

    public function saveFile($file, $path = '/')
    {

        $directory = $this->getUserDirectory($path);

        $ext = FileToolkit::getFileExtension($file);

        $filename = '';
        for ($i = 0; $i<10; $i++) {
            $newFilename = time() . '_' . rand(10000, 99999) . ".$ext";
            if (!file_exists($directory . '/' . $newFilename)) {
                $filename = $newFilename;
                break;
            }
        }

        if (empty($filename)) {
            throw new \RuntimeException('生成文件名失败！');
        }

        return $file->move($directory, $filename);
    }


    private function getUserDirectory($path)
    {
        if (strpos($path, '../')!== false or strpos($path, '..\\') !== false ) {
            throw new \RuntimeException('路径不正确。');
        }
        $userId = $this->user['id'];
        $path = trim($path, '/\\');

        $directory = rtrim("{$this->diskDirectory}/u{$userId}/{$path}", '/\\');

        return $directory;
    }

}


class DiskFileSerialize
{
    public static function serialize(array $file)
    {
        if (isset($file['formats'])) {
            $file['formats'] = !empty($file['formats']) ? $file['formats'] : array();
            $file['formats'] = json_encode($file['formats']);
        }
        return $file;
    }

    public static function unserialize(array $file = null)
    {
        if (empty($file)) {
            return null;
        }
        $file['formats'] = json_decode($file['formats'], true);
        return $file;
    }

    public static function unserializes(array $files)
    {
        return array_map(function($file) {
            return LessonSerialize::unserialize($file);
        }, $files);
    }
}