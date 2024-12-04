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

        if (isset($fields['remark'])) {
            $this->getActivityService()->updateActivity($id, ['remark' => $fields['remark'] ?: '']);
        }

        if (isset($fields['tagIds'])) {
            $this->getLiveActivityService()->updateLiveActivityWithoutEvent($activity['ext']['id'], ['replayTagIds' => $fields['tagIds'] ?: []]);
        }

        if (isset($fields['replayPublic'])) {
            $this->getLiveActivityService()->updateLiveActivityWithoutEvent($activity['ext']['id'], ['replayPublic' => $fields['replayPublic'] ? 1 : 0]);
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
        if (!empty($conditions['courseCategoryId'])) {
            $conditions['categoryId'] = $conditions['courseCategoryId'];
        }
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
                'replayId' => $replay['replayId'],
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
            return $this->trans('site.twig.extension.time_interval.minute', ['%diff%' => round($time / 60, 1)]);
        }

        return $this->trans('site.twig.extension.time_interval.hour_minute', ['%diff_hour%' => floor($time / 3600), '%diff_minute%' => round($time % 3600 / 60)]);
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
}
