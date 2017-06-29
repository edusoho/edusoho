<?php

namespace Biz\File\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface FileImplementor
{
    const CONVERT_STATUS_PENDING = 'waiting';
    const CONVERT_STATUS_SUCCESS = 'success';
    const CONVERT_STATUS_NOT = 'none';
    const CONVERT_STATUS_ERROR = 'error';

    public function getFile($file);

    public function getFullFile($file);

    public function getDefaultHumbnails($globalId);

    public function getThumbnail($globalId, $options);

    public function getStatistics($options);

    public function player($globalId, $ssl = false);

    public function addFile($targetType, $targetId, array $fileInfo = array(), UploadedFile $originalFile = null);

    public function reconvert($globalId, $options);

    public function getUploadAuth($params);

    public function deleteFile($file);

    public function moveFile($targetType, $targetId, UploadedFile $originalFile = null, $data = array());

    //FileImplementor2
    public function getFileByGlobalId($globalId);

    public function prepareUpload($params);

    public function initUpload($file);

    public function resumeUpload($hash, $params);

    public function updateFile($file, $fields);

    public function getDownloadFile($id, $ssl = false);

    public function findFiles($files, $conditions);

    public function finishedUpload($file, $params);

    public function search($conditions);

    public function download($globalId);
}
