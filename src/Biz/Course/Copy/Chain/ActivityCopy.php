<?php

namespace Biz\Course\Copy\Chain;

use Biz\Activity\Config\Activity;
use Biz\Activity\Dao\ActivityDao;
use Biz\Course\Copy\AbstractEntityCopy;

class ActivityCopy extends AbstractEntityCopy
{
    protected function copyEntity($source, $config = [])
    {
        $courseId = $source['id'];
        $newCourseId = $config['newCourse']['id'];
        if (empty($config['newCourseSetId'])) {
            $courseSetId = $config['newCourse']['courseSetId'];
        } else {
            $courseSetId = $config['newCourseSetId'];
        }
        $isCopy = $config['isCopy'];
        // 查询出course下所有activity，新增并保留新旧activity id，用于填充newTask的activityId
        $activities = $this->getActivityDao()->findByCourseId($courseId);

        if (empty($activities)) {
            return [];
        }

        $activityMap = [];
        foreach ($activities as $activity) {
            $newActivity = $this->filterFields($activity);

            $newActivity['fromUserId'] = $this->biz['user']['id'];
            $newActivity['fromCourseId'] = $newCourseId;
            $newActivity['fromCourseSetId'] = $courseSetId;
            $newActivity['copyId'] = $isCopy || 'live' == $newActivity['mediaType'] ? $activity['id'] : 0;

            $ext = $this->getActivityConfig($activity['mediaType'])->copy($activity, [
                'refLiveroom' => $activity['fromCourseSetId'] != $courseSetId,
                'newActivity' => $newActivity,
                'isCopy' => $isCopy,
            ]);
            //对于testpaper，mediaId指向testpaper_activity.id
            if (!empty($ext)) {
                $newActivity['mediaId'] = $ext['id'];
            }

            if ('live' == $newActivity['mediaType'] && !$isCopy) { // 教学计划复制
                $newActivity['startTime'] = time();
                $newActivity['endTime'] = $newActivity['startTime'] + $newActivity['length'] * 60;
            } elseif ('live' == $newActivity['mediaType'] && $isCopy) { // 班级课程复制
                $newActivity['startTime'] = $activity['startTime'];
                $newActivity['endTime'] = $activity['endTime'];
            }
            $newActivity = $this->getActivityDao()->create($newActivity);

            $this->copyMaterial($activity, [
                'newActivity' => $newActivity,
                'newCourseId' => $newCourseId,
                'newCourseSetId' => $courseSetId,
                'isCopy' => $isCopy,
            ]);
            $activityMap[$activity['id']] = $newActivity['id'];
        }

        return $activityMap;
    }

    private function copyMaterial($source, $config)
    {
        $materialCopy = new ActivityMaterialCopy($this->biz);

        return $materialCopy->copy($source, $config);
    }

    protected function getFields()
    {
        return [
            'mediaType',
            'title',
            'remark',
            'content',
            'length',
            'startTime',
            'endTime',
            'finishType',
            'finishData',
        ];
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

    /**
     * @return ActivityDao
     */
    protected function getActivityDao()
    {
        return $this->biz->dao('Activity:ActivityDao');
    }
}
