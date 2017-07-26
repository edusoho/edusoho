<?php

namespace Biz\Course\Copy\Chain;

use Biz\Activity\Service\TestpaperActivityService;

class ActivityTestpaperCopy extends TestpaperCopy
{
    /*
     * - $source = $activity
     * - $config: newActivity, isCopy
     * */
    protected function copyEntity($source, $config = array())
    {
        if ($source['mediaType'] != 'testpaper') {
            return array();
        }

        // 同课程下复制 不需要创建新的试卷
        if ($source['fromCourseSetId'] === $config['newCourseSetId']) {
            $activity = $this->getTestpaperActivityService()->getActivity($source['mediaId']);

            return $this->getTestpaperService()->getTestpaperByIdAndType($activity['mediaId'], $source['mediaType']);
        }

        return $this->doCopyTestpaper($source, $config['newCourseSetId'], $config['newCourseId'], $config['isCopy']);
    }

    public function doCopyTestpaper($activity, $newCourseSetId, $newCourseId, $isCopy)
    {
        $mediaType = $activity['mediaType'];
        $testpaperId = 0;

        if ($mediaType === 'testpaper') {
            $testpaperActivity = $this->getActivityConfig($mediaType)->get($activity['mediaId']);
            $testpaperId = $testpaperActivity['mediaId'];
        } elseif ($mediaType === 'homework' || $mediaType === 'exercise') {
            $testpaperId = $activity['mediaId'];
        }

        if ($testpaperId <= 0) {
            return null;
        }

        $testpaper = $this->getTestpaperService()->getTestpaperByIdAndType($testpaperId, $mediaType);
        if (empty($testpaper)) {
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

        $this->doCopyTestpaperItems(array($testpaper['id']), array($newTestpaper['copyId'] => $newTestpaper), $isCopy);

        return $newTestpaper;
    }

    public function getActivityConfig($type)
    {
        return $this->biz["activity_type.{$type}"];
    }

    /**
     * @return TestpaperActivityService
     */
    protected function getTestpaperActivityService()
    {
        return $this->biz->service('Activity:TestpaperActivityService');
    }
}
