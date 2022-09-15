<?php

namespace Biz\Activity\Service;

use Biz\Activity\Config\Activity;

interface ActivityService
{
    public function getActivity($id, $fetchMedia = false);

    public function getActivityByCopyIdAndCourseSetId($copyId, $courseSetId);

    public function findActivities($ids, $fetchMedia = false, $showCloud = 1);

    public function findActivitiesByCourseIdAndType($courseId, $type, $fetchMedia = false);

    public function findActivitiesByCourseSetId($courseSetId);

    public function findActivitiesByCourseIdsAndType($courseIds, $type, $fetchMedia = false);

    public function findActivitiesByCourseIdsAndTypes($courseIds, $types, $fetchMedia = false);

    public function findActivitiesByCourseSetIdAndType($courseSetId, $type, $fetchMedia = false);

    public function findActivitiesByCourseSetIdsAndType($courseSetIds, $type, $fetchMedia = false);

    public function findActivitiesByCourseSetIdsAndTypes($courseSetIds, $types, $fetchMedia = false);

    public function findActivitiesByMediaIdsAndMediaType($mediaIds, $mediaType);

    /**
     * 创建之前检查完整性
     *
     * @param $activityType
     * @param $params
     *
     * @return mixed
     */
    public function preCreateCheck($activityType, $fields);

    /**
     * 更新之前检查完整性
     *
     * @param $activityType
     * @param $params
     *
     * @return mixed
     */
    public function preUpdateCheck($activityId, $fields);

    public function createActivity($activity);

    public function updateActivity($id, $fields);

    public function deleteActivity($id);

    public function search($conditions, $orderBy, $start, $limit, $columns = []);

    public function count($conditions);

    /**
     * @param string $type 活动类型
     *
     * @return Activity
     */
    public function getActivityConfig($type);

    public function trigger($activityId, $name, $data = []);

    public function isFinished($activityId);

    public function findActivitySupportVideoTryLook($courseIds);

    public function isLiveFinished($activityId);

    public function checkLiveStatus($courseId, $activityId);

    public function findFinishedLivesWithinOneDay();

    public function getActivityFinishCondition($activity);

    public function getByMediaIdAndMediaTypeAndCopyId($mediaId, $mediaType, $copyId);

    public function getByMediaIdAndMediaType($mediaId, $mediaType);

    public function getByMediaIdAndMediaTypeAndCourseId($mediaId, $mediaType, $courseId);

    public function findManageReplayActivityIds($conditions);

    public function getActivityByAnswerSceneId($answerSceneId);

    public function orderAssessmentSubmitNumber($userIds, $answerSceneId);
}
