<?php

namespace Biz\Activity\Copy;

use Biz\AbstractCopy;
use Biz\Activity\Config\Activity;
use Biz\Activity\Dao\ActivityDao;
use Biz\Testpaper\Dao\TestpaperDao;

class ActivityCopy extends AbstractCopy
{
    public function preCopy($source, $options)
    {
        // TODO: Implement preCopy() method.
    }

    public function doCopy($source, $options)
    {
        $course = $options['originCourse'];
        $newCourse = $options['newCourse'];
        $newCourseSet = $options['newCourseSet'];
        $activities = $this->getActivityDao()->findByCourseId($course['id']);
        if (empty($activities)) {
            return array();
        }
        $activityMap = array();
        foreach ($activities as $activity) {
            $newActivity = $this->partsFields($activity);

            $newActivity['fromUserId'] = $this->biz['user']['id'];
            $newActivity['fromCourseId'] = $newCourse['id'];
            $newActivity['fromCourseSetId'] = $newCourseSet['id'];
            $newActivity['copyId'] = $activity['id'];

            $config = $this->getActivityConfig($activity['mediaType']);
            $testId = 0;
            if (in_array($activity['mediaType'], array('testpaper'))) {
                $originalActivityTestpaper = $config->get($activity['mediaId']);
                $activityTestpaper = $this->getTestpaperDao()->getTestpaperByCopyIdAndCourseSetId($originalActivityTestpaper['mediaId'], $newCourseSet['id']);
                $testId = $activityTestpaper['id'];
            }
            $ext = $config->copy($activity, array(
                'refLiveroom' => false,
                'testId' => $testId,
                'newActivity' => $newActivity,
                'isCopy' => true,
            ));

            if (!empty($ext)) {
                $newActivity['mediaId'] = $ext['id'];
            }

            if ($newActivity['mediaType'] == 'live') { //直播
                $newActivity['startTime'] = time();
                $newActivity['endTime'] = $newActivity['startTime'] + $newActivity['length'] * 60;
            }

            $newActivity = $this->getActivityDao()->create($newActivity);
            $options['newActivity'] = $newActivity;
            $options['originActivity'] = $activity;
            $this->doChildrenProcess($source, $options);
            $activityMap[$activity['id']] = $newActivity['id'];
        }
    }

    protected function doChildrenProcess($source, $options)
    {
        $childrenNodes = $this->getChildrenNodes();
        foreach ($childrenNodes as $childrenNode) {
            $CopyClass = $childrenNode['class'];
            $copyClass = new $CopyClass($this->biz, $childrenNode, isset($childrenNode['auto']) ? $childrenNode['auto'] : true);
            $copyClass->copy($source, $options);
        }
    }

    protected function getFields()
    {
        return array(
            'mediaType',
            'title',
            'remark',
            'content',
            'length',
            'startTime',
            'endTime',
            'finishType',
            'finishData',
        );
    }

    /**
     * @return TestpaperDao
     */
    protected function getTestpaperDao()
    {
        return $this->biz->dao('Testpaper:TestpaperDao');
    }

    /**
     * @return ActivityDao
     */
    private function getActivityDao()
    {
        return $this->biz->dao('Activity:ActivityDao');
    }

    /**
     * @param  $type
     *
     * @return Activity
     */
    private function getActivityConfig($type)
    {
        return $this->biz["activity_type.{$type}"];
    }
}
