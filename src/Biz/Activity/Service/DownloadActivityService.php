<?php

namespace Biz\Activity\Service;

interface DownloadActivityService
{
    public function downloadActivityFile($courseId, $activityId, $materialId);
}
