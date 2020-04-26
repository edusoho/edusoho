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
    protected function syncEntity($source, $config = array())
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
            return array();
        }

        $activityMap = array();
        $testpaperSyncMap = array(0);
        $newUploadFiles = ArrayToolkit::index($config['newUploadFiles'], 'syncId');
        foreach ($activities as $activity) {
            $newActivity = $this->filterFields($activity);

            $newActivity['fromUserId'] = $this->biz['user']['id'];
            $newActivity['fromCourseId'] = $newCourseId;
            $newActivity['fromCourseSetId'] = $courseSetId;
            $newActivity['copyId'] = 0;
            $newActivity['syncId'] = $activity['id'];

            //create testpaper
            $testId = 0;
            if (in_array($activity['mediaType'], array('testpaper', 'homework', 'exercise')) && !empty($activity['testpaper'])) {
                if (!in_array($activity['testpaper']['id'], array_keys($testpaperSyncMap))) {
                    $activityTestpaperCopy = new ActivityTestpaperSync($this->biz);
                    $testpaper = $activityTestpaperCopy->sync($activity, array(
                        'newCourseId' => $newCourseId,
                        'newCourseSetId' => $courseSetId,
                        'isCopy' => false,
                        'questionSyncIds' => array_merge(
                            ArrayToolkit::column($source['childrenQuestions'], 'id'),
                            ArrayToolkit::column($source['parentQuestions'], 'id')
                        ),
                    ));
                    $testpaperSyncMap[$activity['testpaper']['id']] = $testpaper['id'];
                    $testId = $testpaper['id'];
                } else {
                    $testId = $testpaperSyncMap[$activity['testpaper']['id']];
                }
            }

            //create activity config
            $activityConfig = $this->getSyncActivityConfig($activity['mediaType']);
            $ext = $activityConfig->sync($activity, array(
                'refLiveroom' => $activity['fromCourseSetId'] != $courseSetId,
                'testId' => $testId,
                'newActivity' => $newActivity,
                'isCopy' => $isCopy,
                'newUploadFiles' => $newUploadFiles,
            ));
            //对于testpaper，mediaId指向testpaper_activity.id
            if (!empty($ext)) {
                $newActivity['mediaId'] = $ext['id'];
            }

            if ('live' == $newActivity['mediaType']) { // 教学计划复制
                $newActivity['startTime'] = time();
                $newActivity['endTime'] = $newActivity['startTime'] + $newActivity['length'] * 60;
            }
            if (in_array($activity['mediaType'], array('exercise', 'homework'))) {
                $newActivity['mediaId'] = $testId;
            }
            $newActivity = $this->getActivityDao()->create($newActivity);
            if ('download' == $newActivity['mediaType']) {
                $this->syncDownloadMaterials($newActivity);
            }
            $activityMap[$activity['id']] = $newActivity['id'];
        }

        return $activityMap;
    }

    protected function syncDownloadMaterials($newActivity)
    {
        $materials = $this->getMaterialService()->searchMaterials(
            array(
                'courseId' => $newActivity['fromCourseId'],
                'courseSetId' => $newActivity['fromCourseSetId'],
                'lessonId' => 0,
                'source' => 'coursematerial',
            ), null, 0, PHP_INT_MAX);
        foreach ($materials as $material) {
            $this->getMaterialDao()->update($material['id'], array('lessonId' => $newActivity['id']));
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

    protected function updateEntityToLastedVersion($source, $config = array())
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
        $existActivities = $this->getActivityDao()->search(array('fromCourseId' => $newCourseId), array(), 0, PHP_INT_MAX);
        if (empty($activities)) {
            foreach ($existActivities as $existActivity) {
                $this->getActivityService()->deleteActivity($existActivity['id']);
            }

            return array();
        }

        // 1、判断这个activity是不是已经有了，没有就增加，存在就update,不存在就传入参数，进行删除
        $activityMap = array();
        $testpaperSyncMap = array(0);
        $newUploadFiles = ArrayToolkit::index($config['newUploadFiles'], 'syncId');
        $existActivities = ArrayToolkit::index($existActivities, 'syncId');
        foreach ($activities as $activity) {
            $newActivity = $this->filterFields($activity);

            $newActivity['fromUserId'] = $this->biz['user']['id'];
            $newActivity['fromCourseId'] = $newCourseId;
            $newActivity['fromCourseSetId'] = $courseSetId;
            $newActivity['copyId'] = 0;
            $newActivity['syncId'] = $activity['id'];

            //create testpaper
            $testId = 0;
            if (in_array($activity['mediaType'], array('testpaper', 'homework', 'exercise')) && !empty($activity['testpaper'])) {
                if (!in_array($activity['testpaper']['id'], array_keys($testpaperSyncMap))) {
                    $activityTestpaperCopy = new ActivityTestpaperSync($this->biz);
                    $testpaper = $activityTestpaperCopy->updateEntityToLastedVersion($activity, array(
                        'newCourseId' => $newCourseId,
                        'newCourseSetId' => $courseSetId,
                        'isCopy' => false,
                        'questionSyncIds' => array_merge(
                            ArrayToolkit::column($source['childrenQuestions'], 'id'),
                            ArrayToolkit::column($source['parentQuestions'], 'id')
                        ),
                    ));
                    $testpaperSyncMap[$activity['testpaper']['id']] = $testpaper['id'];
                    $testId = $testpaper['id'];
                } else {
                    $testId = $testpaperSyncMap[$activity['testpaper']['id']];
                }
            }

            //create activity config
            $activityConfig = $this->getSyncActivityConfig($activity['mediaType']);
            $ext = $activityConfig->updateToLastedVersion($activity, array(
                'refLiveroom' => $activity['fromCourseSetId'] != $courseSetId,
                'testId' => $testId,
                'newActivity' => $newActivity,
                'isCopy' => $isCopy,
                'newUploadFiles' => $newUploadFiles,
            ));
            //对于testpaper，mediaId指向testpaper_activity.id
            if (!empty($ext)) {
                $newActivity['mediaId'] = $ext['id'];
            }

            if ('live' == $newActivity['mediaType']) { // 教学计划复制
                $newActivity['startTime'] = time();
                $newActivity['endTime'] = $newActivity['startTime'] + $newActivity['length'] * 60;
            }
            if (in_array($activity['mediaType'], array('exercise', 'homework'))) {
                $newActivity['mediaId'] = $testId;
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
        return $this->biz["sync_activity_type.{$type}"];
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
