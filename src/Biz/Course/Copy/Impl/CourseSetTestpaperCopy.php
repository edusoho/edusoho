<?php

namespace Biz\Course\Copy\Impl;

use Topxia\Common\ArrayToolkit;

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
        return $this->doCopyTestpaper($config['newCourseSet'], $source['id'], $config['isCopy']);
    }

    private function doCopyTestpaper($newCourseSet, $courseId, $isCopy)
    {
        $testpapers = $this->getActivityService()->findActivitiesByCourseIdAndType($courseId, 'testpaper');

        $testpaperExt = $this->getTestpaperActivityService()->findActivitiesByIds(ArrayToolkit::column($testpapers, 'mediaId'));

        $testpaperIds = ArrayToolkit::column($testpaperExt, 'mediaId');
        if (empty($testpaperIds)) {
            return array();
        }

        $testpapers = $this->getTestpaperService()->findTestpapersByIds($testpaperIds);
        if (empty($testpapers)) {
            return array();
        }

        $newTestpapers = array();
        foreach ($testpapers as $testpaper) {
            $newTestpaper                = $this->baseCopyTestpaper($testpaper, $isCopy);
            $newTestpaper['courseSetId'] = $newCourseSet['id'];
            $newTestpaper['courseId']    = 0;
            $newTestpaper['target']      = 'course-'.$newCourseSet['id'];

            $newTestpaper = $this->getTestpaperService()->createTestpaper($newTestpaper);
            $this->doCopyTestpaperItems($testpaper, $newTestpaper, $isCopy);

            $newTestpapers[] = $newTestpaper;
        }

        return $newTestpapers;
    }

    protected function getTestpaperActivityService()
    {
        return $this->biz->service('Activity:TestpaperActivityService');
    }

    protected function getActivityService()
    {
        return $this->biz->service('Activity:ActivityService');
    }
}
