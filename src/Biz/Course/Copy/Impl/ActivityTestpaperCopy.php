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
        return $this->doCopyTestpaper($source, $config['newActivity'], $config['isCopy']);
    }

    public function doCopyTestpaper($activity, $newActivity, $isCopy)
    {
        $mediaType   = $newActivity['mediaType'];
        $testpaperId = 0;
        
        if ($mediaType == 'testpaper') {
            $testpaperActivity = $this->getActivityConfig($mediaType)->get($activity['mediaId']);
            $testpaperId       = $testpaperActivity['mediaId'];
        } elseif ($mediaType == 'homework' || $mediaType == 'exercise') {
            $testpaperId = $activity['mediaId'];
        }

        if ($testpaperId <= 0) {
            return null;
        }

        $testpaper = $this->getTestpaperService()->getTestpaper($activity['mediaId']);
        if (empty($testpaper) || $testpaper['copyId'] > 0) {
            return null;
        }

        $newTestpaper                = $this->baseCopyTestpaper($testpaper, $isCopy);
        $newTestpaper['courseSetId'] = $newActivity['fromCourseSetId'];
        $newTestpaper['courseId']    = $newActivity['fromCourseId'];

        $newTestpaper = $this->getTestpaperService()->createTestpaper($newTestpaper);

        $this->doCopyTestpaperItems($testpaper, $newTestpaper, $isCopy);

        return $newTestpaper;
    }

    public function getActivityConfig($type)
    {
        return $this->biz["activity_type.{$type}"];
    }
}
