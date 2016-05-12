<?php
namespace Topxia\Service\File;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface FileImplementor
{
    public function getFile($file);

    public function addFile($targetType, $targetId, array $fileInfo = array(), UploadedFile $originalFile = null);

    public function convertFile($file, $status, $result = null, $callback = null);

    public function saveConvertResult($file, array $result = array());

    public function deleteFile($file);

    public function makeUploadParams($params);

    public function reconvertFile($file, $convertCallback, $pipeline = null);

    public function getMediaInfo($key, $mediaType);

    //FileImplementor2
    public function getFileByGlobalId($globalId);

    public function prepareUpload($params);

    public function initUpload($file);

    public function resumeUpload($hash, $params);

    public function updateFile($file, $fields);

    public function getDownloadFile($id);

    public function findFiles($files, $conditions);

    public function finishedUpload($file, $params);

    public function search($conditions);

    public function synData($conditions);

}
