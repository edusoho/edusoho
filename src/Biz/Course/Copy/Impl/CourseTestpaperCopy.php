<?php

namespace Biz\Course\Copy\Impl;

use Biz\Testpaper\Dao\TestpaperDao;

/**
 * Class CourseTestpaperCopy.
 *
 * @deprecated
 * @see ActivityTestpaperCopy
 */
class CourseTestpaperCopy extends TestpaperCopy
{
    public function __construct($biz)
    {
        parent::__construct($biz, 'course-testpaper');
    }

    /*
     * - $source = $activity
     * - $config:
     * */
    protected function copyEntity($source, $config = array())
    {
        return $this->doCopyTestpaper($config['newCourse'], $source['courseSetId'], $source['id'], $config['isCopy']);
    }

    private function doCopyTestpaper($newCourse, $courseSetId, $courseId, $isCopy)
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

            $newTestpaper = $this->baseCopyTestpaper($testpaper, $isCopy);
            $newTestpaper['courseSetId'] = $newCourse['courseSetId'];
            $newTestpaper['courseId'] = $newCourse['id'];

            $newTestpaper = $this->getTestpaperDao()->create($newTestpaper);
            $this->doCopyTestpaperItems($testpaper, $newTestpaper, $isCopy);
            $newTestpapers[] = $newTestpaper;
        }

        return $newTestpapers;
    }

    /**
     * @return TestpaperDao
     */
    protected function getTestpaperDao()
    {
        return $this->biz->dao('Testpaper:TestpaperDao');
    }
}
