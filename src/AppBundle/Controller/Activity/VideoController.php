<?php

namespace AppBundle\Controller\Activity;

use Biz\Course\Service\CourseService;
use Biz\Activity\Service\ActivityService;
use Biz\Task\Service\TaskResultService;
use Biz\User\UserException;
use Symfony\Component\HttpFoundation\Request;

class VideoController extends BaseActivityController implements ActivityActionInterface
{
    public function showAction(Request $request, $activity)
    {
        $type = $this->getActivityService()->getActivityConfig($activity['mediaType']);
        $video = $type->get($activity['mediaId']);
        $watchStatus = $type->getWatchStatus($activity);
        if ('error' === $watchStatus['status']) {
            return $this->render('activity/video/limit.html.twig', array(
                'watchStatus' => $watchStatus,
            ));
        }

        $video = $type->prepareMediaUri($video);

        return $this->render('activity/video/show.html.twig', array(
            'activity' => $activity,
            'video' => $video,
        ));
    }

    public function previewAction(Request $request, $task)
    {
        $activity = $this->getActivityService()->getActivity($task['activityId'], $fetchMedia = true);
        $course = $this->getCourseService()->getCourse($task['courseId']);
        $type = $this->getActivityService()->getActivityConfig($activity['mediaType']);

        $activity['ext'] = $type->prepareMediaUri($activity['ext']);
        $context = $this->prepareContext($request, $course, $activity, $task);

        return $this->render('activity/video/preview.html.twig', array(
            'activity' => $activity,
            'course' => $course,
            'task' => $task,
            'user' => $this->getCurrentUser(),
            'context' => $context,
        ));
    }

    /**
     * 获取当前视频活动的文件来源.
     *
     * @param  $activity
     *
     * @return mixed
     */
    protected function getMediaSource($activity)
    {
        return $activity['ext']['mediaSource'];
    }

    public function editAction(Request $request, $id, $courseId)
    {
        $activity = $this->getActivityService()->getActivity($id, $fetchMedia = true);
        $activity = $this->fillMinuteAndSecond($activity);

        return $this->render('activity/video/modal.html.twig', array(
            'activity' => $activity,
            'courseId' => $courseId,
        ));
    }

    public function createAction(Request $request, $courseId)
    {
        return $this->render('activity/video/modal.html.twig', array(
            'courseId' => $courseId,
        ));
    }

    protected function fillMinuteAndSecond($activity)
    {
        if (!empty($activity['length'])) {
            $activity['minute'] = (int) ($activity['length'] / 60);
            $activity['second'] = (int) ($activity['length'] % 60);
        }

        return $activity;
    }

    public function finishConditionAction(Request $request, $activity)
    {
        $video = $this->getActivityService()->getActivityConfig($activity['mediaType'])->get($activity['mediaId']);

        return $this->render('activity/video/finish-condition.html.twig', array('video' => $video));
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return TaskResultService
     */
    protected function getTaskResultService()
    {
        return $this->createService('Task:TaskResultService');
    }

    public function watchAction(Request $request, $courseId, $id)
    {
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            $this->createNewException(UserException::UN_LOGIN());
        }

        $activity = $this->getActivityService()->getActivity($id);

        $isLimit = $this->setting('magic.lesson_watch_limit');
        if ($isLimit) {
            $type = $this->getActivityService()->getActivityConfig($activity['mediaType']);
            $watchStatus = $type->getWatchStatus($activity);

            return $this->createJsonResponse($watchStatus);
        }

        return $this->createJsonResponse(array('status' => 'ok'));
    }

    private function prepareContext($request, $course, $activity, $task)
    {
        $context = array();
        $file = $activity['ext']['file'];
        if (empty($task['isFree']) && 'self' == $activity['ext']['mediaSource'] && 'cloud' == $file['storage']) {
            $context['hideQuestion'] = 1;
            $context['hideSubtitle'] = 0;

            if (!empty($course['tryLookable'])) {
                $context['starttime'] = $request->query->get('starttime');
                $context['hideBeginning'] = $request->query->get('hideBeginning', false);
                $context['watchTimeLimit'] = $course['tryLookLength'] * 60;
            }
        }

        return $context;
    }
}
