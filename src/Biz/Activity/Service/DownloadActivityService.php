<?php

namespace Biz\Activity\Service;

interface DownloadActivityService
{
    public function createDownloadFileRecord($file);

    public function downloadActivityFile($activityId, $downloadFileId);
}