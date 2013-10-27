<?php

namespace Topxia\Service\Util;

interface CloudClient
{
    public function generateUploadToken($bucket, array $params);

    public function download($bucket, $key, $duration = 3600);

    public function getBucket();

    public function getVideoConvertCommands();

    public function getAudioConvertCommands();

}