<?php

namespace Biz\Course\Copy\Impl;

class CourseTestpaperCopy extends TestpaperCopy
{
    public function __construct($biz)
    {
        $this->biz = $biz;
        parent::__construct($biz, 'course-testpaper');
    }

    /*
     * - $source = $activity
     * - $config:
     * */
    protected function _copy($source, $config = array())
    {
        $this->addError('CourseTestpaperCopy', 'copy source:'.json_encode($source));
        return $this->doCopyTestpaper($config['newCourse'], $source['courseSetId'], $source['id']);
    }

    private function doCopyTestpaper($newCourse, $courseSetId, $courseId = 0)
    {
        $testpapers = $this->getTestpaperDao()->search(array('courseSetId' => $courseSetId, 'courseId' => $courseId), array(), 0, PHP_INT_MAX);
        if (empty($testpapers)) {
            return array();
        }
        $newTestpapers = array();
        foreach ($testpapers as $testpaper) {
            if ($testpaper['courseId'] > 0) {
                continue;
            }

            $newTestpaper                = $this->baseCopyTestpaper($testpaper);
            $newTestpaper['courseSetId'] = $newCourse['courseSetId'];
            $newTestpaper['courseId']    = $newCourse['id'];

            $newTestpaper = $this->getTestpaperDao()->create($newTestpaper);
            $this->doCopyTestpaperItems($testpaper, $newTestpaper);
            $newTestpapers[] = $newTestpaper;
        }

        return $newTestpapers;
    }
}
