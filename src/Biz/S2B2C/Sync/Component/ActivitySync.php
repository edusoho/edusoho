<?php

namespace Biz\S2B2C\Sync\Component;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Dao\ActivityDao;
use Biz\Activity\Service\ActivityService;
use Biz\Course\Dao\CourseMaterialDao;
use Biz\Course\Service\MaterialService;
use Biz\S2B2C\Sync\Component\Activity\Activity;

class ActivitySync extends AbstractEntitySync
{
    protected function syncEntity($source, $config = [])
    {
        $newCourseId = $config['newCourse']['id'];
        if (empty($config['newCourseSetId'])) {
            $courseSetId = $config['newCourse']['courseSetId'];
        } else {
            $courseSetId = $config['newCourseSetId'];
        }
        $isCopy = $config['isCopy'];
        // 获取课程所有的activities
        $activities = $source['activityList'];
        if (empty($activities)) {
            return [];
        }

        $activityMap = [];
        $newUploadFiles = ArrayToolkit::index($config['newUploadFiles'], 'syncId');
        foreach ($activities as $activity) {
            $newActivity = $this->filterFields($activity);

            $newActivity['fromUserId'] = $this->biz['user']['id'];
            $newActivity['fromCourseId'] = $newCourseId;
            $newActivity['fromCourseSetId'] = $courseSetId;
            $newActivity['copyId'] = 0;
            $newActivity['syncId'] = $activity['id'];

            //create activity config
            $activityConfig = $this->getSyncActivityConfig($activity['mediaType']);

            if (empty($activityConfig)) {
                continue;
            }
            $ext = $activityConfig->sync($activity, [
                'refLiveroom' => $activity['fromCourseSetId'] != $courseSetId,
                'newActivity' => $newActivity,
                'isCopy' => $isCopy,
                'newUploadFiles' => $newUploadFiles,
            ]);
            //对于testpaper，mediaId指向testpaper_activity.id
            if (!empty($ext)) {
                $newActivity['mediaId'] = $ext['id'];
            }

            $newActivity = $this->getActivityDao()->create($newActivity);
            if ('download' == $newActivity['mediaType']) {
                $this->syncDownloadMaterials($newActivity);
            }
            $activityMap[$activity['id']] = $newActivity['id'];
        }

        return $activityMap;
    }

    /**
     * @param $newActivity
     * 这里的问题是，默认认为当前lessonId的material都是接下来要处理的activity对应的ID了
     */
    protected function syncDownloadMaterials($newActivity)
    {
        $materials = $this->getMaterialService()->searchMaterials(
            [
                'courseId' => $newActivity['fromCourseId'],
                'courseSetId' => $newActivity['fromCourseSetId'],
                'lessonId' => 0,
                'source' => 'coursematerial',
            ], null, 0, PHP_INT_MAX);
        foreach ($materials as $material) {
            $this->getMaterialDao()->update($material['id'], ['lessonId' => $newActivity['id']]);
        }
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

    protected function updateEntityToLastedVersion($source, $config = [])
    {
        $newCourseId = $config['newCourse']['id'];
        if (empty($config['newCourseSetId'])) {
            $courseSetId = $config['newCourse']['courseSetId'];
        } else {
            $courseSetId = $config['newCourseSetId'];
        }
        $isCopy = $config['isCopy'];
        // 获取课程所有的activities
        $activities = $source['activityList'];
        $existActivities = $this->getActivityDao()->search(['fromCourseId' => $newCourseId], [], 0, PHP_INT_MAX);
        if (empty($activities)) {
            foreach ($existActivities as $existActivity) {
                $this->getActivityService()->deleteActivity($existActivity['id']);
            }

            return [];
        }

        // 1、判断这个activity是不是已经有了，没有就增加，存在就update,不存在就传入参数，进行删除
        $activityMap = [];

        $newUploadFiles = ArrayToolkit::index($config['newUploadFiles'], 'syncId');
        $existActivities = ArrayToolkit::index($existActivities, 'syncId');
        foreach ($activities as $activity) {
            $newActivity = $this->filterFields($activity);

            $newActivity['fromUserId'] = $this->biz['user']['id'];
            $newActivity['fromCourseId'] = $newCourseId;
            $newActivity['fromCourseSetId'] = $courseSetId;
            $newActivity['copyId'] = 0;
            $newActivity['syncId'] = $activity['id'];

            //create activity config
            $activityConfig = $this->getSyncActivityConfig($activity['mediaType']);
            if (empty($activityConfig)) {
                continue;
            }
            $ext = $activityConfig->updateToLastedVersion($activity, [
                'refLiveroom' => $activity['fromCourseSetId'] != $courseSetId,
                'newActivity' => $newActivity,
                'isCopy' => $isCopy,
                'newUploadFiles' => $newUploadFiles,
            ]);
            //对于testpaper，mediaId指向testpaper_activity.id
            if (!empty($ext)) {
                $newActivity['mediaId'] = $ext['id'];
            }

            if (!empty($existActivities[$activity['id']])) {
                $newActivity = $this->getActivityDao()->update($existActivities[$activity['id']]['id'], $newActivity);
            } else {
                $newActivity = $this->getActivityDao()->create($newActivity);
            }
            if ('download' == $newActivity['mediaType']) {
                $this->syncDownloadMaterials($newActivity);
            }
            $activityMap[$activity['id']] = $newActivity['id'];
        }

        return $activityMap;
    }

    /**
     * @param  $type
     *
     * @return Activity
     */
    private function getSyncActivityConfig($type)
    {
        if (!$this->biz->offsetExists("s2b2c.sync_activity_type.{$type}")) {
            return null;
        }

        return $this->biz["s2b2c.sync_activity_type.{$type}"];
    }

    /**
     * @return ActivityDao
     */
    protected function getActivityDao()
    {
        return $this->biz->dao('Activity:ActivityDao');
    }

    /**
     * @return MaterialService
     */
    protected function getMaterialService()
    {
        return $this->biz->service('Course:MaterialService');
    }

    /**
     * @return CourseMaterialDao
     */
    protected function getMaterialDao()
    {
        return $this->biz->dao('Course:CourseMaterialDao');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->biz->service('Activity:ActivityService');
    }
}
