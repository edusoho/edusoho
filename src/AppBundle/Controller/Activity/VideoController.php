<?php

namespace AppBundle\Controller\Activity;

use Biz\Course\Service\CourseService;
use Biz\Activity\Service\ActivityService;
use Biz\Task\Service\TaskResultService;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Component\MediaParser\ParserProxy;

class VideoController extends BaseActivityController implements ActivityActionInterface
{
    public function showAction(Request $request, $activity)
    {
        $video = $this->getActivityService()->getActivityConfig($activity['mediaType'])->get($activity['mediaId']);
        $watchStatus = $this->getWatchStatus($activity);
        if ('error' === $watchStatus['status']) {
            return $this->render('activity/video/limit.html.twig', array(
                'watchStatus' => $watchStatus,
            ));
        }

        $video = $this->prepareMediaUri($video);

        return $this->render('activity/video/show.html.twig', array(
            'activity' => $activity,
            'video' => $video,
        ));
    }

    public function previewAction(Request $request, $task)
    {
        $activity = $this->getActivityService()->getActivity($task['activityId'], $fetchMedia = true);
        $course = $this->getCourseService()->getCourse($task['courseId']);

        $activity['ext'] = $this->prepareMediaUri($activity['ext']);
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

    /**
     * get the information if the video can be watch.
     *
     * @param $task
     */
    protected function getWatchStatus($activity)
    {
        $user = $this->getCurrentUser();
        $watchTime = $this->getTaskResultService()->getWatchTimeByActivityIdAndUserId($activity['id'], $user['id']);

        $course = $this->getCourseService()->getCourse($activity['fromCourseId']);
        $watchStatus = array('status' => 'ok');
        if ($course['watchLimit'] > 0 && $this->setting('magic.lesson_watch_limit')) {
            //只有视频课程才限制观看时长
            if (empty($course['watchLimit']) || 'video' !== $activity['mediaType']) {
                return array('status' => 'ignore');
            }

            $watchLimitTime = $activity['length'] * $course['watchLimit'];
            if (empty($watchTime)) {
                return array('status' => 'ok', 'watchedTime' => 0, 'watchLimitTime' => $watchLimitTime);
            }
            if ($watchTime < $watchLimitTime) {
                return array('status' => 'ok', 'watchedTime' => $watchTime, 'watchLimitTime' => $watchLimitTime);
            }

            return array('status' => 'error', 'watchedTime' => $watchTime, 'watchLimitTime' => $watchLimitTime);
        }

        return $watchStatus;
    }

    public function watchAction(Request $request, $courseId, $id)
    {
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException();
        }

        $activity = $this->getActivityService()->getActivity($id);

        $isLimit = $this->setting('magic.lesson_watch_limit');
        if ($isLimit) {
            $watchStatus = $this->getWatchStatus($activity);

            return $this->createJsonResponse($watchStatus);
        }

        return $this->createJsonResponse(array('status' => 'ok'));
    }

    private function prepareMediaUri($video)
    {
        if ('self' != $video['mediaSource']) {
            $proxy = new ParserProxy();
            $video = $proxy->prepareMediaUriForPc($video);
        }

        return $video;
    }

    private function prepareContext($request, $course, $activity, $task)
    {
        $context = array();
        $file = $activity['ext']['file'];
        if (empty($task['isFree']) && $activity['ext']['mediaSource'] == 'self' && 'cloud' == $file['storage']) {
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
