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
     * - $config:
     * */
    protected function _copy($source, $config = array())
    {
        return $this->doCopyTestpaper($source, $config['isCopy']);
    }

    public function doCopyTestpaper($activity, $isCopy)
    {
        if (!in_array($activity['mediaType'], array('homework', 'testpaper', 'exercise'))) {
            return null;
        }

        $testpaper = $this->getTestpaperService()->getTestpaper($activity['mediaId']);

        if (empty($testpaper)) {
            return null;
        }

        $newTestpaper                = $this->baseCopyTestpaper($testpaper, $isCopy);
        $newTestpaper['courseSetId'] = $activity['fromCourseSetId'];
        $newTestpaper['courseId']    = $activity['fromCourseId'];

        $newTestpaper = $this->getTestpaperService()->createTestpaper($newTestpaper);

        $this->doCopyTestpaperItems($testpaper, $newTestpaper, $isCopy);

        return $newTestpaper;
    }
}
