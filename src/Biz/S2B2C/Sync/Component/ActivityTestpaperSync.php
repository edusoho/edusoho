<?php

namespace Biz\S2B2C\Sync\Component;

use Biz\Activity\Service\TestpaperActivityService;

class ActivityTestpaperSync extends TestpaperSync
{
    /*
     * - $source = $activity
     * - $config: newActivity, isCopy
     * */
    protected function syncEntity($source, $config = array())
    {
        if (!in_array($source['mediaType'], array('testpaper', 'homework', 'exercise'))) {
            return array();
        }

        return $this->doSyncTestpaper($source, $config['newCourseSetId'], $config['newCourseId'], $config['isCopy'], $config['questionSyncIds']);
    }

    public function doSyncTestpaper($activity, $newCourseSetId, $newCourseId, $isCopy, $questionSyncIds)
    {
        $testpaper = $activity['testpaper'];
        if (empty($testpaper)) {
            return null;
        }

        $newTestpaper = $this->baseSyncTestpaper($testpaper);
        $newTestpaper['courseSetId'] = $newCourseSetId;
        $newTestpaper['courseId'] = $newCourseId;

        $newTestpaper = $this->getTestpaperService()->createTestpaper($newTestpaper);
        $newTestpaper['items'] = $testpaper['items'];

        $this->doSyncTestpaperItems(array($newTestpaper['syncId'] => $newTestpaper), $isCopy, $questionSyncIds);

        return $newTestpaper;
    }

    public function getActivityConfig($type)
    {
        return $this->biz["activity_type.{$type}"];
    }

    protected function updateEntityToLastedVersion($source, $config = array())
    {
        if (!in_array($source['mediaType'], array('testpaper', 'homework', 'exercise'))) {
            return array();
        }

        $testpaper = $source['testpaper'];
        if (empty($testpaper)) {
            return null;
        }

        $newTestpaper = $this->baseSyncTestpaper($testpaper);
        $newTestpaper['courseSetId'] = $config['newCourseSetId'];
        $newTestpaper['courseId'] = $config['newCourseId'];

        $exitTestpaper = $this->getTestpaperDao()->search(array('courseSetId' => $newTestpaper['courseSetId'], 'syncId' => $newTestpaper['syncId']), array(), 0, 1);

        $newTestpaper['updatedUserId'] = $this->biz['user']['id'];
        if (!empty($exitTestpaper)) {
            $newTestpaper = $this->getTestpaperDao()->update($exitTestpaper[0]['id'], $newTestpaper);
            $newTestpaper['items'] = $testpaper['items'];
            $this->doUpdateTestpaperItems(array($newTestpaper['syncId'] => $newTestpaper), $config['isCopy'], $config['questionSyncIds']);
        } else {
            $newTestpaper = $this->getTestpaperService()->createTestpaper($newTestpaper);
            $newTestpaper['items'] = $testpaper['items'];
            $this->doSyncTestpaperItems(array($newTestpaper['syncId'] => $newTestpaper), $config['isCopy'], $config['questionSyncIds']);
        }

        return $newTestpaper;
    }

    /**
     * @return TestpaperActivityService
     */
    protected function getTestpaperActivityService()
    {
        return $this->biz->service('Activity:TestpaperActivityService');
    }
}
