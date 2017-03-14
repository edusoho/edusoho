<?php

namespace Biz\Course\Copy\Impl;

class ActivityTestpaperCopy extends TestpaperCopy
{
    public function __construct($biz)
    {
        parent::__construct($biz, 'activity-testpaper');
    }

    /*
     * - $source = $activity
     * - $config: newActivity, isCopy
     * */
    protected function _copy($source, $config = array())
    {
        // 同课程下复制 不需要创建新的试卷
        if ($activity['fromCourseSetId'] === $config['newCourseSetId']) {
            return null;
        }
        return $this->doCopyTestpaper($source, $config['newCourseSetId'], $config['newCourseId'], $config['isCopy']);
    }

    public function doCopyTestpaper($activity, $newCourseSetId, $newCourseId, $isCopy)
    {
        $mediaType = $activity['mediaType'];
        $testpaperId = 0;

        if ($mediaType == 'testpaper') {
            $testpaperActivity = $this->getActivityConfig($mediaType)->get($activity['mediaId']);
            $testpaperId = $testpaperActivity['mediaId'];
        } elseif ($mediaType == 'homework' || $mediaType == 'exercise') {
            $testpaperId = $activity['mediaId'];
        }

        if ($testpaperId <= 0) {
            return null;
        }

        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperId);
        if (empty($testpaper) || $testpaper['copyId'] > 0) {
            return null;
        }

        $existed = $this->getTestpaperService()->getTestpaperByCopyIdAndCourseSetId($testpaperId, $newCourseSetId);

        if (!empty($existed)) {
            return $existed; //已复制过，不要重复复制
        }
        $newTestpaper = $this->baseCopyTestpaper($testpaper, $isCopy);
        $newTestpaper['courseSetId'] = $newCourseSetId;
        $newTestpaper['courseId'] = $newCourseId;

        $newTestpaper = $this->getTestpaperService()->createTestpaper($newTestpaper);

        $this->doCopyTestpaperItems($testpaper, $newTestpaper, $isCopy);

        return $newTestpaper;
    }

    public function getActivityConfig($type)
    {
        return $this->biz["activity_type.{$type}"];
    }
}
