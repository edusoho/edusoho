<?php

namespace ApiBundle\Api\Resource\LiveReplay;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Activity\ActivityException;
use Biz\Activity\Service\ActivityService;
use Biz\Activity\Service\LiveActivityService;
use Biz\Common\CommonException;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\LiveReplayService;
use Biz\User\Service\UserService;

class LiveReplay extends AbstractResource
{
    public function get(ApiRequest $request, $id)
    {
        $activity = $this->getActivityService()->getActivity($id);

        if (empty($activity)) {
            ActivityException::NOTFOUND_ACTIVITY();
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
        $activity = $this->getActivityService()->getActivity($id);

        $fields = $this->filterBaseFields($request->request->all());

        if (!empty($fields['remark'])) {
            $this->getActivityService()->updateActivity($id, ['remark' => $fields['remark']]);
        }

        if (!empty($fields['replayPublic'])) {
            $this->getLiveActivityService()->updateLiveActivity($id, ['replayPublic' => $fields['remark']], $activity);
        }

        return ['success' => true];
    }

    public function remove(ApiRequest $request)
    {
        $ids = $request->request->get('ids', []);
        $realDelete = $request->request->get('realDelete');

        if (empty($ids)) {
            ActivityException::NOTFOUND_ACTIVITY();
        }

        if (!is_array($ids)) {
            CommonException::FIELDS_FORMAT_ERROR();
        }

        foreach ($ids as $id) {
            if ($realDelete) {
                $this->getLiveReplayService()->deleteReplayByLessonId($id);
            } else {
                $this->getLiveReplayService()->updateReplayByLessonId($id, ['courseId' => 0]);
            }
        }

        return ['success' => true];
    }

    protected function filterBaseFields($fields)
    {
        $fields = ArrayToolkit::parts($fields, ['tagIds', 'remark', 'replayPublic']);

        return $fields;
    }

    public function search(ApiRequest $request)
    {
        $conditions = $request->query->all();
        $conditions = $this->filterReplayCondition($conditions);
        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $activities = $this->getActivityService()->search($conditions, ['createdTime' => 'DESC'], $offset, $limit);
        $liveActivities = $this->getActivityService()->findActivities(ArrayToolkit::column($activities, 'id'), true);
        $liveActivities = $this->handleActivityReplay($liveActivities);

        return $this->makePagingObject($liveActivities, $this->getActivityService()->count($conditions), $offset, $limit);
    }

    protected function handleActivityReplay($liveActivities)
    {
        $lessonIds = $activitiesList = [];
        foreach ($liveActivities as $activity) {
            $lessonIds[] = empty($activity['copyId']) ? $activity['id'] : $activity['copyId'];
        }
        $replays = $this->getLiveReplayService()->findReplaysByLessonIds($lessonIds);
        $replays = array_filter($replays, function ($replay) {
            // 过滤掉被隐藏的录播回放
            return !empty($replay) && !(bool) $replay['hidden'];
        });
        $replays = ArrayToolkit::index($replays, 'lessonId');

        foreach ($liveActivities as $activity) {
            $replay = $replays[$activity['id']];
            if (empty($replay)) {
                continue;
            }
            if (isset($activity['ext'])) {
                $user = $this->getUserService()->getUser($activity['ext']['anchorId']);
                $liveTime = $activity['ext']['liveEndTime'] - $activity['ext']['liveStartTime'];
                $activitiesList[] = [
                    'id' => $activity['id'],
                    'title' => $activity['title'],
                    'liveStartTime' => empty($activity['ext']['liveStartTime']) ? '-' : date('Y-m-d H:i:s', $activity['ext']['liveStartTime']),
                    'liveTime' => empty($liveTime) ? '-' : round($liveTime / 60, 1),
                    'liveSecond' => $liveTime,
                    'replayPublic' => $activity['ext']['replayPublic'],
                    'anchor' => empty($user['nickname']) ? '-' : $user['nickname'],
                    'url' => $this->generateUrl('custom_live_activity_replay_entry', [
                        'courseId' => $activity['fromCourseId'],
                        'activityId' => $activity['id'],
                        'replayId' => $replay['id'],
                    ]),
                ];
            }
        }

        return $activitiesList;
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
            $conditions['fromCourseId'] = $conditions['courseId'];
            unset($conditions['courseId']);
        }

        unset($conditions['tagId']);
        unset($conditions['keyword']);
        unset($conditions['categoryId']);
        $conditions['ids'] = empty($activityIds) ? [-1] : $activityIds;
        $conditions['mediaType'] = 'live';

        return $conditions;
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
