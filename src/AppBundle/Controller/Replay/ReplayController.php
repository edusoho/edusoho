<?php

namespace AppBundle\Controller\Replay;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\BaseController;
use Biz\Activity\Service\ActivityService;
use Biz\Activity\Service\LiveActivityService;
use Biz\Course\Service\CourseSetService;
use Biz\User\Service\UserService;
use Symfony\Component\HttpFoundation\Request;

class ReplayController extends BaseController
{
    public function listAction(Request $request)
    {
        $conditions = $request->query->all();
        $conditions = $this->filterReplayCondition($conditions);
        $paginator = new Paginator(
            $request,
            $this->getActivityService()->count($conditions),
            20
        );
        $activities = $this->getActivityService()->search(
            $conditions, ['createdTime' => 'DESC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $liveActivities = $this->getActivityService()->findActivities(ArrayToolkit::column($activities, 'id'), true);
        $liveActivities = $this->handleActivityReplay($liveActivities);

        return $this->render(
            'live-replay/widget/choose-table.html.twig',
            [
                'activities' => $liveActivities,
                'paginator' => $paginator,
            ]
        );
    }

    protected function handleActivityReplay($liveActivities)
    {
        $activitiesList = [];
        foreach ($liveActivities as $activity) {
            if (isset($activity['ext'])) {
                $user = $this->getUserService()->getUser($activity['ext']['anchorId']);
                $liveTime = $activity['ext']['liveEndTime'] - $activity['ext']['liveStartTime'];
                $activitiesList[] = [
                    'id' => $activity['id'],
                    'title' => $activity['title'],
                    'liveStartTime' => empty($activity['ext']['liveStartTime']) ? '-' : date('Y-m-d H:i:s', $activity['ext']['liveStartTime']),
                    'liveTime' => empty($liveTime) ? '-' : round($liveTime / 60, 1),
                    'liveSecond' => $liveTime,
                    'anchor' => empty($user['nickname']) ? '-' : $user['nickname'],
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

        if (isset($conditions['keyword']) && !empty($conditions['keyword'])) {
            $activityLikeTitle = $this->getActivityService()->findActivitiesLiveByLikeTitle($conditions['keyword']);
            $activityLikeTitleIds = ArrayToolkit::column($activityLikeTitle, 'id');
            $activityIds = array_intersect($activityIds, $activityLikeTitleIds);
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
        return $this->createService('Activity:ActivityService');
    }

    /**
     * @return LiveActivityService
     */
    protected function getLiveActivityService()
    {
        return $this->createService('Activity:LiveActivityService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }
}
