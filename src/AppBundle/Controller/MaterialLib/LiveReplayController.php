<?php

namespace AppBundle\Controller\MaterialLib;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\BaseController;
use Biz\Activity\Service\ActivityService;
use Biz\Activity\Service\LiveActivityService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\LiveReplayService;
use Biz\Taxonomy\Service\TagService;
use Symfony\Component\HttpFoundation\Request;

class LiveReplayController extends BaseController
{
    public function indexAction(Request $request)
    {
        $conditions = $request->query->all();
        $conditions = $this->buildLiveSearchConditions($conditions);
        $conditions['anchorId'] = $this->getCurrentUser()->getId();
        list($replays, $paginator, $activities, $users) = $this->buildLiveSearchData($request, $conditions);

        return $this->render('material-lib/web/live-replay/list.html.twig', [
           'tab' => 'my',
           'tags' => $this->buildTagSelectData(),
           'replays' => $replays,
           'activities' => $activities,
           'users' => $users,
           'paginator' => $paginator,
       ]);
    }

    public function shareListAction(Request $request)
    {
        $conditions = $request->query->all();
        $conditions = $this->buildLiveSearchConditions($conditions);
        $conditions['replayPublic'] = 1;
        list($replays, $paginator, $activities, $users) = $this->buildLiveSearchData($request, $conditions);

        return $this->render('material-lib/web/live-replay/list.html.twig', [
            'tab' => 'share',
            'tags' => $this->buildTagSelectData(),
            'replays' => $replays,
            'activities' => $activities,
            'users' => $users,
            'paginator' => $paginator,
        ]);
    }

    public function shareAction(Request $request, $liveActivityId)
    {
        $live = $this->getLiveActivityService()->getLiveActivity($liveActivityId);
        if ($live['anchorId'] != $this->getCurrentUser()->getId()) {
            return $this->createJsonResponse(['status' => false, 'message' => '你无权进行设置！']);
        }
        if ($live['replayPublic'] > 0) {
            $this->getLiveActivityService()->unShareLiveReplay($liveActivityId);
        } else {
            $this->getLiveActivityService()->shareLiveReplay($liveActivityId);
        }

        return $this->createJsonResponse(['status' => true, 'message' => '操作成功']);
    }

    public function editAction(Request $request, $liveActivityId)
    {
        $live = $this->getLiveActivityService()->getLiveActivity($liveActivityId);
        if ($live['anchorId'] != $this->getCurrentUser()->getId()) {
            return $this->createJsonResponse(['status' => false, 'message' => '你无权进行设置！']);
        }
        if ('POST' == $request->getMethod()) {
            $tags = $request->request->get('tags', '');
            $tagIds = empty($tags) ? [] : explode(',', $tags);
            $this->getLiveActivityService()->updateLiveReplayTags($liveActivityId, $tagIds);

            return $this->createJsonpResponse(true);
        }
        $tags = $this->getTagService()->findTagsByIds($live['replayTagIds']);
        $data = [];
        foreach ($tags as $tag) {
            $data[] = ['id' => $tag['id'], 'name' => $tag['name']];
        }

        return $this->render('material-lib/web/live-replay/update.html.twig', [
            'tags' => $data,
            'live' => $live,
        ]);
    }

    public function removeAction(Request $request, $liveActivityId)
    {
        $live = $this->getLiveActivityService()->getLiveActivity($liveActivityId);
        if ($live['anchorId'] != $this->getCurrentUser()->getId()) {
            return $this->createJsonResponse(['status' => false, 'message' => '你无权进行设置！']);
        }
        $this->getLiveActivityService()->removeLiveReplay($liveActivityId);

        return $this->createJsonResponse(['status' => true, 'message' => '操作成功']);
    }

    protected function buildLiveSearchData($request, $conditions)
    {
        $paginator = new Paginator(
            $request,
            $this->getLiveActivityService()->count($conditions),
            20
        );
        $replays = $this->getLiveActivityService()->search(
            $conditions,
            ['liveStartTime' => 'DESC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $replayIds = ArrayToolkit::column($replays, 'id');
        $activities = $this->getActivityService()->findActivitiesByMediaIdsAndMediaType($replayIds, 'live');
        foreach ($activities as &$activity) {
            $courseReplays = $this->getLiveReplayService()->findReplaysByCourseIdAndLessonId($activity['fromCourseId'], $activity['id']);
            $activity['replayArr'] = empty($courseReplays) ? [] : $courseReplays[0];
        }

        $activities = ArrayToolkit::index($activities, 'mediaId');
        $anchorIds = ArrayToolkit::column($replays, 'anchorId');
        $users = $this->getUserService()->findUsersByIds($anchorIds);

        return [$replays, $paginator, $activities, $users];
    }

    protected function buildLiveSearchConditions($conditions)
    {
        $activityConditions = ['mediaType' => 'live'];
        $liveConditions = ['replayStatus' => 'generated'];
        if (!empty($conditions['startTime'])) {
            $liveConditions['liveStartTime_GT'] = strtotime($conditions['startTime']);
        }
        if (!empty($conditions['endTime'])) {
            $liveConditions['liveEndTime_LT'] = strtotime($conditions['endTime']);
        }

        if (!empty($conditions['categoryId'])) {
            $courses = $this->getCourseService()->searchCourses(['categoryId' => $conditions['categoryId']], [], 0, PHP_INT_MAX, ['id']);
            $activityConditions['courseIds'] = empty($courses) ? [-1] : ArrayToolkit::column($courses, 'id');
        }
        if (!empty($conditions['title'])) {
            $activityConditions['title'] = $conditions['title'];
        }
        $activities = $this->getActivityService()->search($activityConditions, [], 0, PHP_INT_MAX, ['id', 'mediaId']);
        $activityLiveIds = empty($activities) ? [-1] : ArrayToolkit::column($activities, 'mediaId');

        return array_merge($liveConditions, ['ids' => $activityLiveIds, 'replayTagIds' => empty($conditions['tagId']) ? '' : '%|'.$conditions['tagId'].'|%']);
    }

    protected function buildTagSelectData()
    {
        $data = [];
        $tags = $this->getTagService()->findAllTags(0, PHP_INT_MAX);
        foreach ($tags as $tag) {
            $data[$tag['id']] = $tag['name'];
        }

        return $data;
    }

    /**
     * @return LiveReplayService
     */
    protected function getLiveReplayService()
    {
        return $this->createService('Course:LiveReplayService');
    }

    /**
     * @return LiveActivityService
     */
    protected function getLiveActivityService()
    {
        return $this->createService('Activity:LiveActivityService');
    }

    /**
     * @return TagService
     */
    protected function getTagService()
    {
        return $this->createService('Taxonomy:TagService');
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
     * @return ActivityService
     */
    public function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }
}
