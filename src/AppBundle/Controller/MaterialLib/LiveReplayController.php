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
        $conditions['anchorId'] = $this->getCurrentUser()->getId();
        $conditions = $this->buildLiveSearchConditions($conditions);
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
        $conditions['replayPublic'] = 1;

        $conditions = $this->buildLiveSearchConditions($conditions);
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
        $activity = $this->getActivityService()->getByMediaIdAndMediaType($liveActivityId, 'live');
        if (empty($activity)) {
            return $this->createJsonResponse(['status' => false, 'message' => '课时不存在！']);
        }
        $this->getLiveReplayService()->deleteReplayByLessonId($activity['id']);

        return $this->createJsonResponse(['status' => true, 'message' => '操作成功']);
    }

    protected function buildLiveSearchData($request, $conditions)
    {
        $paginator = new Paginator(
            $request,
            $this->getLiveReplayService()->searchCount($conditions),
            20
        );
        $replays = $this->getLiveReplayService()->searchReplays(
            $conditions,
            ['createdTime' => 'desc'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $activityIds = ArrayToolkit::column($replays, 'lessonId');
        $liveActivities = $this->getActivityService()->findActivities($activityIds, true);
        $activities = ArrayToolkit::index($liveActivities, 'id');
        $anchorIds = [];
        foreach ($activities as &$activity) {
            $activity['length'] = $this->timeFormatterFilter($activity['endTime'] - $activity['startTime']);
            $anchorIds[] = $activity['ext']['anchorId'];
        }
        $users = $this->getUserService()->findUsersByIds($anchorIds);

        return [$replays, $paginator, $activities, $users];
    }

    public function timeFormatterFilter($time)
    {
        if ($time <= 3600) {
            return $this->trans('site.twig.extension.time_interval.minute', ['%diff%' => round($time / 60, 1)]);
        }

        return $this->trans('site.twig.extension.time_interval.hour_minute', ['%diff_hour%' => floor($time / 3600), '%diff_minute%' => round($time % 3600 / 60)]);
    }

    protected function buildLiveSearchConditions($conditions)
    {
        if (!empty($conditions['startTime'])) {
            $conditions['startTime'] = strtotime($conditions['startTime']);
        }
        if (!empty($conditions['endTime'])) {
            $conditions['endTime'] = strtotime($conditions['endTime']);
        }
        if (!empty($conditions['tagId'])) {
            $conditions['replayTagId'] = $conditions['tagId'];
        }
        if (!empty($conditions['title'])) {
            $conditions['keywordType'] = 'activityTitle';
            $conditions['keyword'] = $conditions['title'];
        }
        $conditions = ArrayToolkit::parts($conditions, ['startTime', 'endTime', 'replayTagId', 'keywordType', 'keyword', 'categoryId', 'anchorId', 'replayPublic']);
        $activityIds = $this->getActivityService()->findManageReplayActivityIds($conditions);

        return ['lessonIds' => $activityIds, 'hidden' => 0];
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
