<?php

namespace Biz\Activity\Service\Impl;

use Biz\BaseService;
use Biz\Activity\Dao\ActivityDao;
use AppBundle\Common\ArrayToolkit;
use Biz\Common\CommonException;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MaterialService;
use Biz\File\Service\UploadFileService;
use Codeages\Biz\Framework\Event\Event;
use Biz\Course\Service\CourseSetService;
use Biz\Activity\Service\ActivityService;
use Biz\Activity\Service\ActivityLearnLogService;
use Biz\Activity\Listener\ActivityLearnLogListener;
use Biz\Util\EdusohoLiveClient;

class ActivityServiceImpl extends BaseService implements ActivityService
{
    const LIVE_STARTTIME_DIFF_SECONDS = 7200;
    const LIVE_ENDTIME_DIFF_SECONDS = 7200;

    public function getActivity($id, $fetchMedia = false)
    {
        $activity = $this->getActivityDao()->get($id);

        if ($fetchMedia) {
            $activity = $this->fetchMedia($activity);
        }

        return $activity;
    }

    public function getActivityFinishCondition($activity)
    {
        if (ArrayToolkit::requireds($activity, array('mediaType', 'finishType', 'finishData'))) {
            $this->createInvalidArgumentException('params missed');
        }

        $type = $activity['mediaType'];
        $finishType = $activity['finishType'];
        $finishData = $activity['finishData'];

        if ('testpapaer' == $type && 'score' == $finishType) {
            if (!isset($activity['ext'])) {
                $activity = $this->fetchMedia($activity);
            }
            $finishData = $activity['ext']['finishCondition']['finishScore'];
        }

        $text = "mobile.task.finish_tips.{$type}.{$finishType}";

        try {
            $text = $this->trans($text, array('%finishData%' => $finishData));
        } catch (\Exception $e) {
            // 如果新增类型，而翻译文件未配置，会报错
            $text = '';
        }

        return array(
            'type' => $finishType,
            'data' => $finishData,
            'text' => $text,
        );
    }

    public function getActivityByCopyIdAndCourseSetId($copyId, $courseSetId)
    {
        return $this->getActivityDao()->getByCopyIdAndCourseSetId($copyId, $courseSetId);
    }

    public function findActivities($ids, $fetchMedia = false, $showCloud = 1)
    {
        $activities = $this->getActivityDao()->findByIds($ids);

        return $this->prepareActivities($fetchMedia, $activities, $showCloud);
    }

    public function findActivitiesByCourseIdAndType($courseId, $type, $fetchMedia = false)
    {
        $conditions = array(
            'fromCourseId' => $courseId,
            'mediaType' => $type,
        );
        $activities = $this->getActivityDao()->search($conditions, null, 0, 1000);

        return $this->prepareActivities($fetchMedia, $activities);
    }

    public function findActivitiesByCourseSetIdAndType($courseSetId, $type, $fetchMedia = false)
    {
        $conditions = array(
            'fromCourseSetId' => $courseSetId,
            'mediaType' => $type,
        );
        $activities = $this->getActivityDao()->search($conditions, null, 0, 1000);

        return $this->prepareActivities($fetchMedia, $activities);
    }

    public function search($conditions, $orderBy, $start, $limit)
    {
        return $this->getActivityDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function count($conditions)
    {
        return $this->getActivityDao()->count($conditions);
    }

    public function trigger($id, $eventName, $data = array())
    {
        $activity = $this->getActivity($id);

        if (empty($activity)) {
            return false;
        }

        if ('start' == $eventName) {
            $this->biz['dispatcher']->dispatch("activity.{$eventName}", new Event($activity, $data));
        }

        if (isset($data['events']) && array_key_exists('finish', $data['events'])) {
            $tempData['taskId'] = empty($data['taskId']) ? 0 : $data['taskId'];
            $data = $tempData;
            $eventName = 'finish';
        }
        $this->triggerActivityLearnLogListener($activity, $eventName, $data);

        if (empty($data['events'])) {
            $events = array();
        } else {
            $events = $data['events'];
            unset($data['events']);
        }
        foreach ($events as $key => $event) {
            $data = array_merge($event, $data);
            $this->triggerActivityLearnLogListener($activity, $key, $data);
            $this->triggerExtendListener($activity, $key, $data);
        }
        if ('doing' == $eventName || 'finish' == $eventName) {
            $this->biz['dispatcher']->dispatch("activity.{$eventName}", new Event($activity, $data));
        }

        return true;
    }

    protected function triggerActivityLearnLogListener($activity, $eventName, $data)
    {
        $logListener = new ActivityLearnLogListener($this->biz);
        $logData = $this->extractLogData($eventName, $data);
        $logListener->handle($activity, $logData);
    }

    protected function triggerExtendListener($activity, $eventName, $data)
    {
        $activityListener = $this->getActivityConfig($activity['mediaType'])->getListener($eventName);
        if (null !== $activityListener) {
            $activityListener->handle($activity, $data);
        }
    }

    public function preCreateCheck($activityType, $fields)
    {
        $activity = $this->getActivityConfig($activityType);
        $activity->preCreateCheck($fields);
    }

    public function preUpdateCheck($activityId, $fields)
    {
        $activity = $this->getActivity($activityId);

        $activityInstance = $this->getActivityConfig($activity['mediaType']);
        $activityInstance->preUpdateCheck($activity, $fields);
    }

    public function createActivity($fields)
    {
        if ($this->invalidActivity($fields)) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }

        $this->getCourseService()->tryManageCourse($fields['fromCourseId']);
        $activityConfig = $this->getActivityConfig($fields['mediaType']);

        if (empty($fields['mediaId'])) {
            $media = $activityConfig->create($fields);
        }

        if (!empty($media)) {
            $fields['mediaId'] = $media['id'];
        }

        $materials = $this->getMaterialsFromActivity($fields);

        $fields['fromUserId'] = $this->getCurrentUser()->getId();
        $fields = $this->filterFields($fields);
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

        $realActivity = $this->getActivityConfig($savedActivity['mediaType']);

        $materials = $this->getMaterialsFromActivity($fields);
        if (!empty($materials)) {
            $this->syncActivityMaterials($savedActivity, $materials, 'update');
        }

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
            $this->getActivityLearnLogService()->deleteLearnLogsByActivityId($id);
            $result = $this->getActivityDao()->delete($id);
            $this->commit();

            return $result;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function isFinished($id)
    {
        $activity = $this->getActivity($id);
        $activityConfig = $this->getActivityConfig($activity['mediaType']);

        return $activityConfig->isFinished($id);
    }

    protected function syncActivityMaterials($activity, $materials, $mode = 'create')
    {
        if ('delete' === $mode) {
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
                $exists = $this->getMaterialService()->searchMaterials(
                    array(
                        'lessonId' => $activity['id'],
                        'type' => 'course',
                    ),
                    array('createdTime' => 'DESC'),
                    0,
                    PHP_INT_MAX
                );
                $currents = array();
                foreach ($materials as $id => $material) {
                    $currents[] = $this->buildMaterial($material, $activity);
                }

                $dropMaterials = $this->diffMaterials($exists, $currents);
                $addMaterials = $this->diffMaterials($currents, $exists);
                $updateMaterials = $this->dirtyMaterials($exists, $currents);
                foreach ($dropMaterials as $material) {
                    $this->getMaterialService()->deleteMaterial($activity['fromCourseSetId'], $material['id']);
                }
                foreach ($addMaterials as $material) {
                    $this->getMaterialService()->uploadMaterial($material);
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
            'fileId' => intval($material['fileId']),
            'courseId' => $activity['fromCourseId'],
            'courseSetId' => $activity['fromCourseSetId'],
            'lessonId' => $activity['id'],
            'title' => $material['title'],
            'description' => empty($material['summary']) ? '' : $material['summary'],
            'userId' => $this->getCurrentUser()->offsetGet('id'),
            'type' => 'course',
            'source' => 'download' == $activity['mediaType'] ? 'coursematerial' : 'courseactivity',
            'link' => empty($material['link']) ? '' : $material['link'],
            'copyId' => 0, //$fields
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
                if (0 == $value1['fileId']) {
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
                if ((0 != $exist['fileId'] && $exist['fileId'] == $current['fileId'])
                    || (0 == $exist['fileId'] && $exist['link'] == $current['link'])
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
        $fields = ArrayToolkit::parts(
            $fields,
            array(
                'title',
                'remark',
                'mediaId',
                'mediaType',
                'mediaId',
                'content',
                'length',
                'fromCourseId',
                'fromCourseSetId',
                'fromUserId',
                'startTime',
                'endTime',
                'finishType',
                'finishData',
            )
        );

        if (!empty($fields['startTime']) && !empty($fields['length']) && 'testpaper' != $fields['mediaType']) {
            $fields['endTime'] = $fields['startTime'] + $fields['length'] * 60;
        }

        if (empty($fields['mediaType'])) {
            unset($fields['mediaType']);
        }

        return $fields;
    }

    protected function invalidActivity($activity)
    {
        if (!ArrayToolkit::requireds(
            $activity,
            array(
                'title',
                'mediaType',
                'fromCourseId',
                'fromCourseSetId',
            )
        )
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
     *
     * @return array 多维数组
     */
    public function getMaterialsFromActivity($fields)
    {
        if (!empty($fields['materials'])) {
            return json_decode($fields['materials'], true);
        }

        if (!empty($fields['media'])) {
            $media = json_decode($fields['media'], true);
            if (!empty($media['id'])) {
                $media['fileId'] = $media['id'];
                $media['title'] = $media['name'];

                return array($media);
            }
        }
    }

    /**
     * @param  $activity
     *
     * @return mixed
     */
    public function fetchMedia($activity)
    {
        if (!empty($activity['mediaId'])) {
            $activityConfig = $this->getActivityConfig($activity['mediaType']);
            $media = $activityConfig->get($activity['mediaId']);
            $activity['ext'] = $media;

            return $activity;
        }

        return $activity;
    }

    public function fetchMedias($mediaType, $activities, $showCloud = 1)
    {
        $activityConfig = $this->getActivityConfig($mediaType);

        $mediaIds = ArrayToolkit::column($activities, 'mediaId');
        $medias = $activityConfig->find($mediaIds, $showCloud);

        $medias = ArrayToolkit::index($medias, 'id');

        array_walk(
            $activities,
            function (&$activity) use ($medias) {
                //part of the activity have no extension table
                $activity['ext'] = empty($medias[$activity['mediaId']]) ? array() : $medias[$activity['mediaId']];
            }
        );

        return $activities;
    }

    public function findActivitySupportVideoTryLook($courseIds)
    {
        $activities = $this->getActivityDao()->findSelfVideoActivityByCourseIds($courseIds);
        $cloudFiles = $this->findCloudFilesByMediaIds($activities);
        $activities = array_filter($activities, function ($activity) use ($cloudFiles) {
            return !empty($cloudFiles[$activity['fileId']]);
        });

        return $activities;
    }

    public function isLiveFinished($activityId)
    {
        $activity = $this->getActivity($activityId, true);

        if (empty($activity) || empty($activity['ext'])) {
            return true;
        }

        $endLeftSeconds = time() - $activity['endTime'];
        $isEsLive = EdusohoLiveClient::isEsLive($activity['ext']['liveProvider']);

        if ($this->checkLiveFinished($activity)) {
            return true;
        }

        if (EdusohoLiveClient::LIVE_STATUS_CLOSED == $activity['ext']['progressStatus']) {
            return true;
        }

        return false;
    }

    public function checkLiveStatus($courseId, $activityId)
    {
        $activity = $this->getActivity($activityId, true);
        if (empty($activity)) {
            return array('result' => false, 'message' => 'message_response.live_task_not_exist.message');
        }

        if ($activity['fromCourseId'] != $courseId) {
            return array('result' => false, 'message' => 'message_response.illegal_params.message');
        }

        if (empty($activity['ext']['liveId'])) {
            return array('result' => false, 'message' => 'message_response.live_class_not_exist.message');
        }

        if ($activity['startTime'] - time() > self::LIVE_STARTTIME_DIFF_SECONDS) {
            return array('result' => false, 'message' => 'message_response.live_not_start.message');
        }

        if ($this->checkLiveFinished($activity)) {
            return array('result' => false, 'message' => 'message_response.live_over.message');
        }

        return array('result' => true, 'message' => '');
    }

    public function findFinishedLivesWithinTwoHours()
    {
        return $this->getActivityDao()->findFinishedLivesWithinTwoHours();
    }

    public function getActivityConfig($type)
    {
        return $this->biz["activity_type.{$type}"];
    }

    protected function checkLiveFinished($activity)
    {
        $isEsLive = EdusohoLiveClient::isEsLive($activity['ext']['liveProvider']);
        $endLeftSeconds = time() - $activity['endTime'];

        //ES直播结束时间2小时后就自动结束，第三方直播以直播结束时间为准
        $thirdLiveFinished = $endLeftSeconds > 0 && !$isEsLive;
        $esLiveFinished = $isEsLive && $endLeftSeconds > self::LIVE_ENDTIME_DIFF_SECONDS;

        return $thirdLiveFinished || $esLiveFinished;
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
     * @return ActivityLearnLogService
     */
    protected function getActivityLearnLogService()
    {
        return $this->createService('Activity:ActivityLearnLogService');
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

    /**
     * @param $activity
     * @param $eventName
     * @param $data
     *
     * @return mixed
     */
    protected function extractLogData($eventName, $data)
    {
        unset($data['task']);
        $logData = $data;
        $logData['event'] = $eventName;

        return $logData;
    }

    /**
     * @param $fetchMedia
     * @param $activities
     * @param $sortedActivities
     *
     * @return mixed
     */
    protected function prepareActivities($fetchMedia, $activities, $showCloud = 1)
    {
        if (empty($activities)) {
            return $activities;
        }
        $activityGroups = ArrayToolkit::group($activities, 'mediaType');
        if ($fetchMedia) {
            foreach ($activityGroups as $mediaType => $activityGroup) {
                $activityGroups[$mediaType] = $this->fetchMedias($mediaType, $activityGroup, $showCloud);
            }
        }

        $fullActivities = array();
        foreach ($activityGroups as $activityGroup) {
            $fullActivities = array_merge($fullActivities, array_values($activityGroup));
        }

        $activityIds = ArrayToolkit::column($activities, 'id');

        foreach ($fullActivities as $activity) {
            $key = array_search($activity['id'], $activityIds);
            $sortedActivities[$key] = $activity;
        }
        ksort($sortedActivities);

        return $sortedActivities;
    }

    /**
     * @param $activities
     *
     * @return array
     */
    protected function findCloudFilesByMediaIds($activities)
    {
        $fileIds = ArrayToolkit::column($activities, 'fileId');
        $files = $this->getUploadFileService()->findFilesByIds($fileIds);
        $cloudFiles = array_filter($files, function ($file) {
            return 'cloud' === $file['storage'];
        });
        $cloudFiles = ArrayToolkit::index($cloudFiles, 'id');

        return $cloudFiles;
    }
}
