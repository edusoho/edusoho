<?php

namespace ApiBundle\Api\Resource\LiveReplay;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Activity\ActivityException;
use Biz\Activity\Service\ActivityService;
use Biz\Activity\Service\LiveActivityService;
use Biz\Common\CommonException;
use Biz\Course\LiveReplayException;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\LiveReplayService;
use Biz\User\Service\UserService;

class LiveReplay extends AbstractResource
{
    public function get(ApiRequest $request, $id)
    {
        $activity = $this->getActivityService()->getActivity($id);

        if (empty($activity)) {
            throw ActivityException::NOTFOUND_ACTIVITY();
        }

        $liveActivity = $this->getLiveActivityService()->getLiveActivity($activity['mediaId']);

        return [
            'tag' => '',
            'remark' => $activity['remark'],
            'replayPublic' => $liveActivity['replayPublic'],
        ];
    }

    public function update(ApiRequest $request, $id)
    {
        $activity = $this->getActivityService()->getActivity($id, true);

        $fields = $request->request->all();

        if (!empty($fields['remark'])) {
            $this->getActivityService()->updateActivity($id, ['remark' => $fields['remark']]);
        }

        if (!empty($fields['replayPublic']) || !empty($fields['tagIds'])) {
            $this->getLiveActivityService()->updateLiveActivityWithoutEvent($activity['ext']['id'], ['replayTagIds' => empty($fields['tagIds']) ? [] : $fields['tagIds'], 'replayPublic' => $fields['replayPublic']]);
        }

        return ['success' => true];
    }

    public function remove(ApiRequest $request)
    {
        $ids = $request->request->get('ids', []);
        if (empty($ids)) {
            throw LiveReplayException::NOTFOUND_LIVE_REPLAY();
        }

        if (!is_array($ids)) {
            throw CommonException::FIELDS_FORMAT_ERROR();
        }

        foreach ($ids as $id) {
            $this->getLiveReplayService()->deleteReplayByLessonId($id);
        }

        return ['success' => true];
    }

    public function search(ApiRequest $request)
    {
        $conditions = $request->query->all();
        $activityIds = $this->getActivityService()->findManageReplayActivityIds($conditions);
        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $replays = $this->getLiveReplayService()->searchReplays(['lessonIds' => $activityIds, 'hidden' => 0], ['createdTime' => 'desc'], $offset, $limit);
        $replays = $this->handleActivityReplay($replays);

        return $this->makePagingObject($replays, $this->getLiveReplayService()->searchCount(['lessonIds' => $activityIds, 'hidden' => 0]), $offset, $limit);
    }

    protected function handleActivityReplay($replays)
    {
        if (empty($replays)) {
            return [];
        }
        $activityIds = ArrayToolkit::column($replays, 'lessonId');
        $liveActivities = $this->getActivityService()->findActivities($activityIds, true);
        $liveActivities = ArrayToolkit::index($liveActivities, 'id');

        foreach ($replays as $replay) {
            $activity = $liveActivities[$replay['lessonId']];
            $user = $this->getUserService()->getUser($activity['ext']['anchorId']);
            $liveTime = $activity['ext']['liveEndTime'] - $activity['ext']['liveStartTime'];
            $activitiesList[] = [
                'id' => $activity['id'],
                'title' => $activity['title'],
                'liveStartTime' => empty($activity['ext']['liveStartTime']) ? '-' : date('Y-m-d H:i:s', $activity['ext']['liveStartTime']),
                'liveTime' => empty($liveTime) ? '-' : $this->timeFormatterFilter($liveTime),
                'liveSecond' => $liveTime,
                'tag' => $activity['ext']['replayTagIds'],
                'replayPublic' => $activity['ext']['replayPublic'],
                'anchor' => empty($user['nickname']) ? '-' : $user['nickname'],
                'url' => $this->generateUrl('custom_live_activity_replay_entry', [
                    'courseId' => $activity['fromCourseId'],
                    'activityId' => $activity['id'],
                    'replayId' => $replay['id'],
                ]),
            ];
        }

        return $activitiesList;
    }

    public function timeFormatterFilter($time)
    {
        if ($time <= 60) {
            return $this->trans('site.twig.extension.time_interval.minute', ['%diff%' => 0]);
        }

        if ($time <= 3600) {
            return $this->trans('site.twig.extension.time_interval.minute', ['%diff%' => round($time / 60)]);
        }

        return $this->trans('site.twig.extension.time_interval.hour_minute', ['%diff_hour%' => floor($time / 3600), '%diff_minute%' => round($time % 3600 / 60)]);
    }

    protected function filterReplayCondition($conditions)
    {
        $currentUser = $this->getCurrentUser();

        if ($currentUser->isAdmin()) {
            $liveActivity = $this->getLiveActivityService()->findLiveActivitiesByReplayStatus();
        } else {
            $liveActivity = $this->getLiveActivityService()->findLiveActivitiesByIsPublic();
        }

        $activityPublic = [];
        $liveActivitiesIds = ArrayToolkit::column($liveActivity, 'id');
        if (!empty($liveActivitiesIds)) {
            $activityPublic = $this->getActivityService()->findActivitiesByMediaIdsAndMediaType(ArrayToolkit::column($liveActivity, 'id'), 'live');
        }

        $activityCreator = $this->getActivityService()->findActivitiesByCourseSetIdAndType($conditions['courseSetId'], 'live');
        $activityIds = ArrayToolkit::column(array_merge($activityPublic, $activityCreator), 'id');

        if (isset($conditions['categoryId']) && !empty($conditions['categoryId'])) {
            $courseSet = $this->getCourseSetService()->findCourseSetsByCategoryIdAndCreator($conditions['categoryId'], $currentUser->getId());
            $activityCategory = $this->getActivityService()->findActivitiesByCourseSetIdsAndType(ArrayToolkit::column($courseSet, 'id'), 'live');
            $activityCategoryIds = ArrayToolkit::column($activityCategory, 'id');
            $activityIds = array_intersect($activityIds, $activityCategoryIds);
        }

        if (isset($conditions['tagId']) && !empty($conditions['tagId'])) {
            $liveActivity = $this->getLiveActivityService()->findLiveActivitiesByReplayTagId($conditions['tagId']);
            $activityTagIds = ArrayToolkit::column($liveActivity, 'id');
            $activityIds = array_intersect($activityIds, $activityTagIds);
        }

        if (isset($conditions['replayPublic']) && !empty($conditions['replayPublic'])) {
            $liveActivityPublic = $this->getLiveActivityService()->findLiveActivitiesByIsPublic();
            $activityPublicIds = ArrayToolkit::column($liveActivityPublic, 'id');
            $activityIds = array_intersect($activityIds, $activityPublicIds);
        }

        if (isset($conditions['keyword']) && !empty($conditions['keyword'])) {
            $activityLikeTitle = $this->getActivityService()->findActivitiesLiveByLikeTitle($conditions['keyword']);
            $activityLikeTitleIds = ArrayToolkit::column($activityLikeTitle, 'id');
            $activityIds = array_intersect($activityIds, $activityLikeTitleIds);
        }

        if (!empty($conditions['courseId'])) {
            $searchConditions['fromCourseId'] = $conditions['courseId'];
        }

        $searchConditions['ids'] = empty($activityIds) ? [-1] : $activityIds;
        $searchConditions['mediaType'] = 'live';

        return $searchConditions;
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->service('Activity:ActivityService');
    }

    /**
     * @return LiveActivityService
     */
    protected function getLiveActivityService()
    {
        return $this->service('Activity:LiveActivityService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->service('User:UserService');
    }

    /**
     * @return LiveReplayService
     */
    protected function getLiveReplayService()
    {
        return $this->service('Course:LiveReplayService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->service('Course:CourseSetService');
    }
}
