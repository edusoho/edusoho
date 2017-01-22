<?php

namespace Biz\Activity\Service\Impl;

use Biz\BaseService;
use Topxia\Common\ArrayToolkit;
use Biz\Activity\Dao\ActivityDao;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MaterialService;
use Biz\File\Service\UploadFileService;
use Codeages\Biz\Framework\Event\Event;
use Biz\Course\Service\CourseSetService;
use Biz\Activity\Service\ActivityService;
use Biz\Activity\Listener\ActivityLearnLogListener;

class ActivityServiceImpl extends BaseService implements ActivityService
{
    public function getActivity($id, $fetchMedia = false)
    {
        $activity = $this->getActivityDao()->get($id);

        if ($fetchMedia) {
            $activity = $this->fetchMedia($activity);
        }
        return $activity;
    }

    public function findActivities($ids, $fetchMedia = false)
    {
        $activities = $this->getActivityDao()->findByIds($ids);

        if ($fetchMedia) {
            foreach ($activities as &$activity) {
                $activity = $this->fetchMedia($activity);
            }
        }
        return $activities;
    }

    public function findActivitiesByCourseIdAndType($courseId, $type, $fetchMedia = false)
    {
        $conditions = array(
            'fromCourseId' => $courseId,
            'mediaType'    => $type
        );
        $activities = $this->getActivityDao()->search($conditions, null, 0, 1000);

        if ($fetchMedia) {
            foreach ($activities as &$activity) {
                $activity = $this->fetchMedia($activity);
            }
        }
        return $activities;
    }

    public function findActivitiesByCourseSetIdAndType($courseSetId, $type, $fetchMedia = false)
    {
        $conditions = array(
            'fromCourseSetId' => $courseSetId,
            'mediaType'       => $type
        );
        $activities = $this->getActivityDao()->search($conditions, null, 0, 1000);

        if ($fetchMedia) {
            foreach ($activities as &$activity) {
                $activity = $this->fetchMedia($activity);
            }
        }
        return $activities;
    }

    public function trigger($id, $eventName, $data = array())
    {
        $activity = $this->getActivity($id);

        if (empty($activity)) {
            return;
        }

        if ($eventName == 'start') {
            $this->biz['dispatcher']->dispatch("activity.{$eventName}", new Event($activity, $data));
        }

        $this->triggerActivityLearnLogListener($activity, $eventName, $data);
        $this->triggerExtendListener($activity, $eventName, $data);

        if (in_array($eventName, array('doing', 'watching'))) {
            $this->biz['dispatcher']->dispatch("activity.{$eventName}", new Event($activity, $data));
        }
    }

    protected function triggerActivityLearnLogListener($activity, $eventName, $data)
    {
        $logListener = new ActivityLearnLogListener($this->biz);

        $logData          = $data;
        $logData['event'] = $activity['mediaType'].'.'.$eventName;
        $logListener->handle($activity, $logData);
    }

    protected function triggerExtendListener($activity, $eventName, $data)
    {
        $activityListener = $this->getActivityConfig($activity['mediaType'])->getListener($eventName);
        if (!is_null($activityListener)) {
            $activityListener->handle($activity, $data);
        }
    }

    public function createActivity($fields)
    {
        if ($this->invalidActivity($fields)) {
            throw $this->createInvalidArgumentException('activity is invalid');
        }

        $this->getCourseService()->tryManageCourse($fields['fromCourseId']);

        $materials = $this->getFileDataFromActivity($fields);

        $activityConfig = $this->getActivityConfig($fields['mediaType']);
        $media          = $activityConfig->create($fields);

        if (!empty($media)) {
            $fields['mediaId'] = $media['id'];
        }

        $fields['fromUserId']  = $this->getCurrentUser()->getId();
        $fields                = $this->filterFields($fields);
        $fields['createdTime'] = time();

        $activity = $this->getActivityDao()->create($fields);

        if (!empty($materials)) {
            $this->syncActivityMaterials($activity, $materials, 'create');
        }

        $listener = $activityConfig->getListener('activity.created');
        if (!empty($listener)) {
            $listener->handle($activity, array());
        }

        return $activity;
    }

    public function updateActivity($id, $fields)
    {
        $savedActivity = $this->getActivity($id);

        $this->getCourseService()->tryManageCourse($savedActivity['fromCourseId']);

        $materials = $this->getFileDataFromActivity($fields);
        if (!empty($materials)) {
            $this->syncActivityMaterials($savedActivity, $materials, 'update');
        }

        $realActivity = $this->getActivityConfig($savedActivity['mediaType']);

        if (!empty($savedActivity['mediaId'])) {
            $media = $realActivity->update($savedActivity['mediaId'], $fields, $savedActivity);

            if (!empty($media)) {
                $fields['mediaId'] = $media['id'];
            }
        }

        $fields = $this->filterFields($fields);
        return $this->getActivityDao()->update($id, $fields);
    }

    public function deleteActivity($id)
    {
        $activity = $this->getActivity($id);

        try {
            $this->beginTransaction();
            $this->getCourseService()->tryManageCourse($activity['fromCourseId']);

            $this->syncActivityMaterials($activity, array(), 'delete');

            $activityConfig = $this->getActivityConfig($activity['mediaType']);
            $activityConfig->delete($activity['mediaId']);

            $this->getActivityDao()->delete($id);
            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }

        return true;
    }

    public function isFinished($id)
    {
        $activity       = $this->getActivity($id);
        $activityConfig = $this->getActivityConfig($activity['mediaType']);

        return $activityConfig->isFinished($id);
    }

    protected function syncActivityMaterials($activity, $materials, $mode = 'create')
    {
        if ($mode === 'delete') {
            $this->getMaterialService()->deleteMaterialsByLessonId($activity['id']);
            return;
        }

        if (empty($materials)) {
            return;
        }

        switch ($mode) {
            case 'create':
                foreach ($materials as $id => $material) {
                    $this->getMaterialService()->uploadMaterial($this->buildMaterial($material, $activity));
                }
                break;
            case 'update':
                $exists   = $this->getMaterialService()->searchMaterials(array('lessonId' => $activity['id']), array('createdTime' => 'DESC'), 0, PHP_INT_MAX);
                $currents = array();
                foreach ($materials as $id => $material) {
                    $currents[] = $this->buildMaterial($material, $activity);
                }

                $dropMaterials   = $this->diffMaterials($exists, $currents);
                $addMaterials    = $this->diffMaterials($currents, $exists);
                $updateMaterials = $this->dirtyMaterials($exists, $currents);
                foreach ($dropMaterials as $material) {
                    $this->getMaterialService()->deleteMaterial($activity['fromCourseSetId'], $material['id']);
                }
                foreach ($addMaterials as $material) {
                    $this->getMaterialService()->addMaterial($material, $material);
                }
                foreach ($updateMaterials as $material) {
                    $this->getMaterialService()->updateMaterial($material['id'], $material, $material);
                }
                break;
            default:
                break;
        }
    }

    protected function buildMaterial($material, $activity)
    {
        return array(
            'fileId'      => intval($material['id']),
            'courseId'    => $activity['fromCourseId'],
            'courseSetId' => $activity['fromCourseSetId'],
            'lessonId'    => $activity['id'],
            'title'       => $material['name'],
            'description' => empty($material['summary']) ? '' : $material['summary'],
            'userId'      => $this->getCurrentUser()->offsetGet('id'),
            'type'        => 'course',
            'source'      => $activity['mediaType'] == 'download' ? 'coursematerial' : 'courseactivity',
            'link'        => empty($material['link']) ? '' : $material['link'],
            'copyId'      => 0 //$fields
        );
    }

    protected function diffMaterials($arr1, $arr2)
    {
        $diffs = array();
        if (empty($arr2)) {
            return $arr1;
        }
        foreach ($arr1 as $value1) {
            $contained = false;
            foreach ($arr2 as $value2) {
                if ($value1['fileId'] == 0) {
                    $contained = $value1['link'] == $value2['link'];
                } else {
                    $contained = $value1['fileId'] == $value2['fileId'];
                }
                if ($contained) {
                    break;
                }
            }
            if (!$contained) {
                $diffs[] = $value1;
            }
        }
        return $diffs;
    }

    protected function dirtyMaterials($exists, $currents)
    {
        $diffs = array();
        if (empty($arr2)) {
            return $diffs;
        }
        foreach ($exists as $exist) {
            foreach ($currents as $current) {
                //如果fileId存在则匹配fileId，否则匹配link
                if (($exist['fileId'] != 0 && $exist['fileId'] == $current['fileId'])
                    || ($exist['fileId'] == 0 && $exist['link'] == $current['link'])
                ) {
                    $current['id'] = $exist['id'];
                    if (empty($current['description'])) {
                        $current['description'] = $exist['description'];
                    }
                    $diffs[] = $current;
                    break;
                }
            }
        }
        return $diffs;
    }

    protected function filterFields($fields)
    {
        $fields = ArrayToolkit::parts($fields, array(
            'title',
            'remark',
            'mediaId',
            'mediaType',
            'content',
            'length',
            'fromCourseId',
            'fromCourseSetId',
            'fromUserId',
            'startTime',
            'endTime'
        ));

        if (isset($fields['startTime']) && isset($fields['length']) && $fields['mediaType'] != 'testpaper') {
            $fields['endTime'] = $fields['startTime'] + $fields['length'] * 60;
        }

        if (empty($fields['mediaType'])) {
            unset($fields['mediaType']);
        }

        return $fields;
    }

    protected function invalidActivity($activity)
    {
        if (!ArrayToolkit::requireds($activity, array(
            'title',
            'mediaType',
            'fromCourseId',
            'fromCourseSetId'
        ))
        ) {
            return true;
        }
        $activity = $this->getActivityConfig($activity['mediaType']);
        if (!is_object($activity)) {
            return true;
        }
        return false;
    }

    /**
     * @param  $fields
     * @return array
     */
    public function getFileDataFromActivity($fields)
    {
        $materials = array();
        if (!empty($fields['materials'])) {
            $materials = json_decode($fields['materials'], true);
        }
        if (empty($materials) && !empty($fields['media'])) {
            $materials[] = json_decode($fields['media'], true);
        }
        if (empty($materials) && !empty($fields['ext'])) {
            $ext = $fields['ext'];
            if (!empty($ext['mediaId'])) {
                $file        = $this->getUploadFileService()->getFile($ext['mediaId']);
                $materials[] = array(
                    'id'   => $file['id'],
                    'name' => $file['filename']
                );
            }
        }

        return $materials;
    }

    /**
     * @param  $activity
     * @return mixed
     */
    public function fetchMedia($activity)
    {
        if (!empty($activity['mediaId'])) {
            $activityConfig  = $this->getActivityConfig($activity['mediaType']);
            $media           = $activityConfig->get($activity['mediaId']);
            $activity['ext'] = $media;
            return $activity;
        }
        return $activity;
    }

    public function getActivityConfig($type)
    {
        return $this->biz["activity_type.{$type}"];
    }

    /**
     * @return MaterialService
     */
    protected function getMaterialService()
    {
        return $this->createService('Course:MaterialService');
    }

    /**
     * @return ActivityDao
     */
    protected function getActivityDao()
    {
        return $this->createDao('Activity:ActivityDao');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
    }
}
