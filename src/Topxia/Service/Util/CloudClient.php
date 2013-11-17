<?php

namespace Topxia\Service\Util;

interface CloudClient
{
    public function generateUploadToken($bucket, array $params = array());

    public function download($bucket, $key, $duration = 3600, $asFilename=null);

    public function getBucket();

    public function getVideoConvertCommands();

    public function getAudioConvertCommands();

    public function getVideoInfo($bucket, $key);

    public function getAudioInfo($bucket, $key);

    public function removeFile($key);

    public function getFileUrl($key,$targetId,$targetType);

}