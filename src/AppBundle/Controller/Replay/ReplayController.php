<?php

namespace AppBundle\Controller\Replay;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\BaseController;
use Biz\Activity\Service\ActivityService;
use Biz\Activity\Service\LiveActivityService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\LiveReplayService;
use Biz\User\Service\UserService;
use Symfony\Component\HttpFoundation\Request;

class ReplayController extends BaseController
{
    public function listAction(Request $request)
    {
        $conditions = $request->query->all();
        $conditions['replayTagId'] = empty($conditions['tagId']) ? '' : $conditions['tagId'];
        $activityIds = $this->getActivityService()->findManageReplayActivityIds($conditions);
        $paginator = new Paginator(
            $request,
            $this->getLiveReplayService()->searchCount(['lessonIds' => $activityIds, 'hidden' => 0]),
            20
        );
        $replays = $this->getLiveReplayService()->searchReplays(
            ['lessonIds' => $activityIds, 'hidden' => 0],
            ['createdTime' => 'DESC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $liveActivities = $this->handleActivityReplay($replays);

        return $this->render(
            'live-replay/widget/choose-table.html.twig',
            [
                'activities' => $liveActivities,
                'paginator' => $paginator,
            ]
        );
    }

    protected function handleActivityReplay($replays)
    {
        if (empty($replays)) {
            return [];
        }
        $activityIds = ArrayToolkit::column($replays, 'lessonId');
        $liveActivities = $this->getActivityService()->findActivities($activityIds, true);
        $liveActivities = ArrayToolkit::index($liveActivities, 'id');

        $activitiesList = [];
        foreach ($replays as $replay) {
            $activity = $liveActivities[$replay['lessonId']];
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

        return $activitiesList;
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

    /**
     * @return LiveReplayService
     */
    protected function getLiveReplayService()
    {
        return $this->createService('Course:LiveReplayService');
    }
}
