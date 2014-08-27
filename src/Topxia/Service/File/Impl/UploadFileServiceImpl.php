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
	static $implementor = array(
        'local'=>'File.LocalFileImplementor',
        'cloud' => 'File.CloudFileImplementor',
    );

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
        $file = $this->getFileImplementor($implemtor)->addFile($targetType,$targetId,$fileInfo,$originalFile);
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

        $deleteSubFile = true;
        if (!empty($file['etag'])) {
            $etagCount = $this->getUploadFileDao()->findFilesCountByEtag($file['etag']);
            if ($etagCount > 1) {
                $deleteSubFile = false;
            }
        }

        if (!empty($file['convertParams']['convertor']) && $file['convertParams']['convertor'] == 'HLSEncryptedVideo') {
            $deleteSubFile = true;
        }

        if (!empty($file['convertParams']['convertor']) && $file['convertParams']['convertor'] == 'ppt') {
            $deleteSubFile = true;
        }

        $this->getFileImplementorByFile($file)->deleteFile($file, $deleteSubFile);

        return $this->getUploadFileDao()->deleteFile($id);
    }

    public function deleteFiles(array $ids)
    {
        foreach ($ids as $id) {
            $this->deleteFile($id);
        }
    }

    public function saveConvertResult($id, array $result = array())
    {
        $file = $this->getFile($id);
        if (empty($file)) {
            throw $this->createServiceException("文件(#{$id})不存在，转换失败");
        }

        $file = $this->getFileImplementorByFile($file)->saveConvertResult($file, $result);

        $this->getUploadFileDao()->updateFile($id, array(
            'convertStatus' => $file['convertStatus'],
            'metas2' => json_encode($file['metas2']),
            'updatedTime' => time(),
        ));

        return $this->getFile($id);
    }

    public function saveConvertResult3($id, array $result = array())
    {
        $file = $this->getFile($id);
        if (empty($file)) {
            throw $this->createServiceException("文件(#{$id})不存在，转换失败");
        }
        $file['convertParams']['convertor'] = 'HLSEncryptedVideo';

        $fileNeedUpdateFields = array();

        $file = $this->getFileImplementorByFile($file)->saveConvertResult($file, $result);

        if ($file['convertStatus'] == 'success') {
            $fileNeedUpdateFields['convertParams'] = json_encode($file['convertParams']);
            $fileNeedUpdateFields['metas2'] = json_encode($file['metas2']);
            $fileNeedUpdateFields['updatedTime'] = time();
            $this->getUploadFileDao()->updateFile($id, $fileNeedUpdateFields);
        }

        return $this->getFile($id);
    }

    public function convertFile($id, $status, array $result = array(), $callback = null)
    {
        $statuses = array('none', 'waiting', 'doing', 'success', 'error');
        if (!in_array($status, $statuses)) {
            throw $this->createServiceException('状态不正确，变更文件转换状态失败！');
        }

        $file = $this->getFile($id);
        if (empty($file)) {
            throw $this->createServiceException("文件(#{$id})不存在，转换失败");
        }

        $file = $this->getFileImplementorByFile($file)->convertFile($file, $status, $result, $callback);

        $this->getUploadFileDao()->updateFile($id, array(
            'convertStatus' => $file['convertStatus'],
            'metas2' => $file['metas2'],
            'updatedTime' => time(),
        ));

        return $this->getFile($id);
    }

    public function setFileConverting($id, $convertHash)
    {
        $file = $this->getFile($id);
        if (empty($file)) {
            throw $this->createServiceException('file not exist.');
        }

        // $status = $file['convertStatus'] == 'success' ? 'success' : 'waiting';

        $fields = array(
            'convertStatus' => 'waiting',
            'convertHash' => $convertHash,
            'updatedTime' => time(),
        );

        $this->getUploadFileDao()->updateFile($id, $fields);

        return $this->getFile($id);
    }

    public function makeUploadParams($params)
    {
        return $this->getFileImplementor($params['storage'])->makeUploadParams($params);
    }

    public function reconvertFile($id, $convertCallback)
    {
        $file = $this->getFile($id);
        if (empty($file)) {
            throw $this->createServiceException('file not exist.');
        }

        $convertHash = $this->getFileImplementorByFile($file)->reconvertFile($file, $convertCallback);

        $this->setFileConverting($file['id'], $convertHash);

        return $convertHash;
    }

    public function reconvertOldFile($id, $convertCallback, $pipeline)
    {
        $result = array();

        $file = $this->getFile($id);
        if (empty($file)) {
            return array('error' => 'file_not_found', 'message' => "文件(#{$id})不存在");
        }

        if ($file['storage'] != 'cloud') {
            return array('error' => 'not_cloud_file', 'message' => "文件(#{$id})，不是云文件。");
        }

        if ($file['type'] != 'video') {
            return array('error' => 'not_video_file', 'message' => "文件(#{$id})，不是视频文件。");
        }

        if ($file['targetType'] != 'courselesson') {
            return array('error' => 'not_course_file', 'message' => "文件(#{$id})，不是课时文件。");
        }

        $target = $this->createService('Course.CourseService')->getCourse($file['targetId']);
        if (empty($target)) {
            return array('error' => 'course_not_exist', 'message' => "文件(#{$id})所属的课程已删除。");
        }

        if (!empty($file['convertParams']['convertor']) && $file['convertParams']['convertor'] == 'HLSEncryptedVideo') {
            return array('error' => 'already_converted', 'message' => "文件(#{$id})已转换");
        }

        $fileNeedUpdateFields = array();

        if (!empty($file['convertParams']['convertor']) && $file['convertParams']['convertor'] == 'HLSVideo') {
            $file['convertParams']['hlsKeyUrl'] = 'http://hlskey.edusoho.net/placeholder';
            $file['convertParams']['hlsKey'] = $this->generateKey(16);
            $fileNeedUpdateFields['convertParams'] = json_encode($file['convertParams']);
            $file['convertParams']['convertor'] = 'HLSEncryptedVideo';
        }

        if (empty($file['convertParams'])) {
            $convertParams = array(
                'convertor' => 'HLSEncryptedVideo',
                'segtime' => 10,
                'videoQuality' => 'low',
                'audioQuality' => 'low',
                'video' => array('240k', '440k', '640k'),
                'audio' => array('32k', '48k', '64k'),
                'hlsKeyUrl' => 'http://hlskey.edusoho.net/placeholder',
                'hlsKey' => $this->generateKey(16),
            );

            $file['convertParams'] = $convertParams;

            $convertParams['convertor'] = 'HLSVideo';
            $fileNeedUpdateFields['convertParams'] = json_encode($convertParams);
        }

        $convertHash = $this->getFileImplementorByFile($file)->reconvertFile($file, $convertCallback, $pipeline);
        if (empty($convertHash)) {
            return array('error' => 'convert_request_failed', 'message' => "文件(#{$id})转换请求失败！");
        }

        $fileNeedUpdateFields['convertHash'] = $convertHash;
        $fileNeedUpdateFields['updatedTime'] = time();

        $this->getUploadFileDao()->updateFile($file['id'], $fileNeedUpdateFields);


        $subTarget = $this->createService('Course.CourseService')->findLessonsByTypeAndMediaId('video', $file['id']) ? : array();
        if (!empty($subTarget)) {
            $subTarget = $subTarget[0];
        }

        return array(
            'convertHash' => $convertHash,
            'courseId' => empty($subTarget['courseId']) ? $target['targetId'] : $subTarget['courseId'],
            'lessonId' => empty($subTarget['id']) ? 0 : $subTarget['id'],
        );
    }

    public function getMediaInfo($key, $type)
    {
        return $this->getFileImplementor('cloud')->getMediaInfo($key, $type);
    }

    private function generateKey ($length = 0 )
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

        $key = '';
        for ( $i = 0; $i < 16; $i++ ) {
            $key .= $chars[ mt_rand(0, strlen($chars) - 1) ];
        }
        
        return $key;
    }

    private function getFileImplementorByFile($file)
    {
        return $this->getFileImplementor($file['storage']);
    }

    private function getUploadFileDao()
    {
        return $this->createDao('File.UploadFileDao');
    }

    private function getUserService()
    {
        return $this->createService('User.UserService');
    }

    private function getFileImplementor($key)
    {
        if (!array_key_exists($key, self::$implementor)) {
            throw $this->createServiceException(sprintf("`%s` File Implementor is not allowed.", $key));
        }
        return $this->createService(self::$implementor[$key]);
    }

    private function getLogService()
    {
        return $this->createService('System.LogService');
    }
}
