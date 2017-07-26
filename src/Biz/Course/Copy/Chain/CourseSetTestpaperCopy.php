<?php

namespace Biz\Course\Copy\Chain;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\Activity\Service\TestpaperActivityService;

/**
 * Class CourseSetTestpaperCopy.
 *
 * @deprecated
 * @see ActivityTestpaperCopy
 */
class CourseSetTestpaperCopy extends TestpaperCopy
{
    /*
     * - $source = $activity
     * - $config:
     * */
    protected function copyEntity($source, $config = array())
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

        $user = $this->biz['user'];
        $newTestpapers = array();
        $testpaperIds = array();
        foreach ($testpapers as $testpaper) {
            $newTestpaper = $this->baseCopyTestpaper($testpaper, $isCopy);
            $newTestpaper['courseSetId'] = $newCourseSet['id'];
            $newTestpaper['courseId'] = 0;
            $newTestpaper['target'] = 'course-'.$newCourseSet['id'];
            $newTestpaper['createdUserId'] = $user['id'];
            $newTestpaper['updatedUserId'] = $user['id'];

            $testpaperIds[] = $testpaper['id'];
            $newTestpapers[] = $newTestpaper;
        }

        $this->getTestpaperService()->batchCreateTestpaper($newTestpapers);

        $newTestpapers = $this->getTestpaperService()->searchTestpapers(
            array('courseSetId' => $newCourseSet['id']),
            array(),
            0,
            PHP_INT_MAX
        );
        $newTestpapers = ArrayToolkit::index($newTestpapers, 'copyId');

        $this->doCopyTestpaperItems($testpaperIds, $newTestpapers, $isCopy);

        return $newTestpapers;
    }

    /**
     * @return TestpaperActivityService
     */
    protected function getTestpaperActivityService()
    {
        return $this->biz->service('Activity:TestpaperActivityService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->biz->service('Activity:ActivityService');
    }
}
