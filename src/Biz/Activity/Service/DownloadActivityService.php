<?php

namespace Biz\DownloadActivity\Service;

interface DownloadActivityService
{
    public function createDownloadFileRecord($file);

    public function downloadActivityFile($activityId, $downloadFileId);
}