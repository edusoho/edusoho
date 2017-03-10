<?php

namespace Biz\Activity\Service;

interface DownloadActivityService
{
    public function downloadActivityFile($activityId, $materialId);
}
