<?php

namespace Biz\Course\Copy\Impl;

class CourseSetTestpaperCopy extends TestpaperCopy
{
    public function __construct($biz)
    {
        parent::__construct($biz, 'course-set-testpaper');
    }

    /*
     * - $source = $activity
     * - $config:
     * */
    protected function _copy($source, $config = array())
    {
        return $this->doCopyTestpaper($config['newCourseSet'], $source['courseSetId'], $config['isCopy']);
    }

    private function doCopyTestpaper($newCourseSet, $courseSetId, $isCopy)
    {
        $testpapers = $this->getTestpaperService()->searchTestpapers(array('courseSetId' => $courseSetId), array(), 0, PHP_INT_MAX);
        if (empty($testpapers)) {
            return array();
        }
        $newTestpapers = array();
        foreach ($testpapers as $testpaper) {
            if ($testpaper['courseId'] > 0) {
                continue;
            }

            $newTestpaper                = $this->baseCopyTestpaper($testpaper, $isCopy);
            $newTestpaper['courseSetId'] = $newCourseSet['id'];
            $newTestpaper['courseId']    = 0;

            $newTestpaper = $this->getTestpaperService()->createTestpaper($newTestpaper);
            $this->doCopyTestpaperItems($testpaper, $newTestpaper, $isCopy);

            $newTestpapers[] = $newTestpaper;
        }

        return $newTestpapers;
    }
}
