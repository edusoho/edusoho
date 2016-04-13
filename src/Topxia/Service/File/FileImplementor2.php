<?php
namespace Topxia\Service\File;

interface FileImplementor2
{
    public function getFile($file);

    public function prepareUpload($params);

    public function initUpload($file);

    public function resumeUpload($hash, $params);

    public function updateFile($file, $fields);

    public function getDownloadFile($id);

    public function deleteFile($file);

    public function findFiles($files,$conditions);

    public function finishedUpload($file, $params);

    public function search($conditions);

    public function synData($conditions);
}
