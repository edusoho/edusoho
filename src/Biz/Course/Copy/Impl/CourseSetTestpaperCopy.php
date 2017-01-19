<?php

namespace Biz\Course\Copy\Impl;

class CourseSetTestpaperCopy extends TestpaperCopy
{
    /*
     * - $source = $activity
     * - $config:
     * */
    protected function _copy($source, $config = array())
    {
        $this->addError('CourseSetTestpaperCopy', 'copy source:'.json_encode($source));
        return $this->doCopyTestpaper($config['newCourseSet'], $source['courseSetId']);
    }

    private function doCopyTestpaper($newCourseSet, $courseSetId)
    {
        $testpapers = $this->getTestpaperDao()->search(array('courseSetId' => $courseSetId), array(), 0, PHP_INT_MAX);
        if (empty($testpapers)) {
            return array();
        }
        $newTestpapers = array();
        foreach ($testpapers as $testpaper) {
            if ($testpaper['courseId'] > 0) {
                continue;
            }

            $newTestpaper                = $this->baseCopyTestpaper($testpaper);
            $newTestpaper['courseSetId'] = $newCourseSet['id'];
            $newTestpaper['courseId']    = 0;

            $newTestpaper = $this->getTestpaperDao()->create($newTestpaper);
            $this->doCopyTestpaperItems($testpaper, $newTestpaper);

            $newTestpapers[] = $newTestpaper;
        }

        return $newTestpapers;
    }
}
