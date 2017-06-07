<?php

namespace Biz\Course\Copy\Impl;

use Biz\Activity\Config\Activity;
use Biz\Activity\Dao\ActivityDao;
use Biz\Course\Copy\AbstractEntityCopy;

class ActivityCopy extends AbstractEntityCopy
{
    public function __construct($biz)
    {
        parent::__construct($biz, 'activity');
    }

    protected function copyEntity($source, $config = array())
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
        $activityMap = array();

        if (!empty($activities)) {
            $fields = $this->getFields();
            foreach ($activities as $activity) {
                $newActivity = array(
                    'fromUserId' => $this->biz['user']['id'],
                    'fromCourseId' => $newCourseId,
                    'fromCourseSetId' => $courseSetId,
                    'copyId' => $isCopy ? $activity['id'] : 0,
                );
                foreach ($fields as $field) {
                    if (!empty($activity[$field]) || $activity[$field] == 0) {
                        $newActivity[$field] = $activity[$field];
                    }
                }

                //create testpaper
                $testId = 0;
                if (in_array($activity['mediaType'], array('homework', 'testpaper', 'exercise'))) {
                    $activityTestpaperCopy = new ActivityTestpaperCopy($this->biz);

                    $testpaper = $activityTestpaperCopy->copy($activity, array(
                        'newCourseId' => $newCourseId,
                        'newCourseSetId' => $courseSetId,
                        'isCopy' => $isCopy,
                    ));
                    $testId = $testpaper['id'];
                }

                //create activity config
                $config = $this->getActivityConfig($activity['mediaType']);

                $ext = $config->copy($activity, array(
                    'refLiveroom' => $activity['fromCourseSetId'] != $courseSetId,
                    'testId' => $testId,
                    'newActivity' => $newActivity,
                ));
                //对于testpaper，mediaId指向testpaper_activity.id
                if (!empty($ext)) {
                    $newActivity['mediaId'] = $ext['id'];
                }
                //对于exercise、homework，mediaId指向testpaper.id
                if ($testId > 0 && in_array($activity['mediaType'], array('homework', 'exercise'))) {
                    $newActivity['mediaId'] = $testId;
                }
                if ($newActivity['mediaType'] == 'live' && !$isCopy) { // 教学计划复制
                    // unset($newActivity['startTime']);
                    // unset($newActivity['endTime']);
                    $newActivity['startTime'] = time();
                    $newActivity['endTime'] = $newActivity['startTime'] + $newActivity['length'] * 60;
                } elseif ($newActivity['mediaType'] == 'live' && $isCopy) { // 班级课程复制
                    $newActivity['startTime'] = $activity['startTime'];
                    $newActivity['endTime'] = $activity['endTime'];
                }
                $newActivity = $this->getActivityDao()->create($newActivity);

                $this->copyMaterial($activity, array(
                    'newActivity' => $newActivity,
                    'newCourseId' => $newCourseId,
                    'newCourseSetId' => $courseSetId,
                    'isCopy' => $isCopy,
                ));
                $activityMap[$activity['id']] = $newActivity['id'];
            }
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
        return array(
            'mediaType',
            'title',
            'remark',
            'content',
            'length',
            'startTime',
            'endTime',
        );
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
