<?php

namespace Biz\Course\Copy\Impl;

class ActivityTestpaperCopy extends TestpaperCopy
{
    public function __construct($biz)
    {
        $this->biz = $biz;
        parent::__construct($biz, 'activity-testpaper');
    }

    /*
     * - $source = $activity
     * - $config:
     * */
    protected function _copy($source, $config = array())
    {
        $this->addError('ActivityTestpaperCopy', 'copy source:'.json_encode($source));
        return $this->doCopyTestpaper($source);
    }

    public function doCopyTestpaper($activity)
    {
        if (!in_array($activity['mediaType'], array('homework', 'testpaper', 'exercise'))) {
            return null;
        }

        $testpaper = $this->getTestpaperDao()->get($activity['mediaId']);
        if (empty($testpaper) || $testpaper['lessonId'] == 0) {
            return null;
        }

        $newTestpaper                = $this->baseCopyTestpaper($testpaper);
        $newTestpaper['courseSetId'] = $activity['fromCourseSetId'];
        $newTestpaper['courseId']    = $activity['fromCourseId'];

        $newTestpaper = $this->getTestpaperDao()->create($newTestpaper);
        $this->doCopyTestpaperItems($testpaper, $newTestpaper);

        return $newTestpaper;
    }
}
