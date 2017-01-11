<?php

namespace AppBundle\Controller\Activity;


use AppBundle\Controller\BaseController;
use Biz\Activity\Service\ActivityService;
use Biz\File\Service\UploadFileService;
use Biz\User\Service\TokenService;
use Biz\Util\CloudClientFactory;
use Symfony\Component\HttpFoundation\Request;

class VideoController extends BaseController implements ActivityActionInterface
{
    public function showAction(Request $request, $id, $courseId)
    {
        $activity = $this->getActivityService()->getActivity($id, $fetchMedia = true);

        return $this->render('activity/video/show.html.twig', array(
            'activity' => $activity,
            'courseId' => $courseId
        ));
    }

    public function previewAction(Request $request, $task)
    {
        $activity = $this->getActivityService()->getActivity($task['activityId'], $fetchMedia = true);

        $course = $this->getCourseService()->getCourse($task['courseId']);
        $user   = $this->getCurrentUser();
        $context = array();

        if ($task['mediaSource'] != 'self') {
            if ($task['mediaSource'] == 'youku') {
                $matched = preg_match('/\/sid\/(.*?)\/v\.swf/s', $activity['ext']['mediaUri'], $matches);

                if ($matched) {
                    $task['mediaUri']    = "http://player.youku.com/embed/{$matches[1]}";
                    $task['mediaSource'] = 'iframe';
                }
            } elseif ($task['mediaSource'] == 'tudou') {
                $matched = preg_match('/\/v\/(.*?)\/v\.swf/s', $activity['ext']['mediaUri'], $matches);

                if ($matched) {
                    $task['mediaUri']    = "http://www.tudou.com/programs/view/html5embed.action?code={$matches[1]}";
                    $task['mediaSource'] = 'iframe';
                }
            }
        } else {
            $context['hideQuestion'] = 1;
            $context['hideSubtitle'] = 0;

            if (!$task["isFree"] && !empty($course['tryLookable'])) {
                $context['starttime']      = $request->query->get('starttime');
                $context['hideBeginning']  = $request->query->get('hideBeginning', false);
                $context['watchTimeLimit'] = $course['tryLookLength'] * 60;
            }
        }

        return $this->render('activity/video/preview.html.twig', array(
            'activity' => $activity,
            'course'   => $course,
            'task'     => $task,
            'user'     => $user,
            'context'  => $context
        ));
    }


    /**
     * 获取当前视频活动的文件来源
     * @param $activity
     * @return mediaSource
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
            'courseId' => $courseId
        ));
    }

    public function createAction(Request $request, $courseId)
    {
        return $this->render('activity/video/modal.html.twig', array(
            'courseId' => $courseId
        ));
    }

    protected function fillMinuteAndSecond($activity)
    {
        if (!empty($activity['length'])) {
            $activity['minute'] = intval($activity['length'] / 60);
            $activity['second'] = intval($activity['length'] % 60);
        }
        return $activity;
    }

    public function finishConditionAction($activity)
    {
        return $this->render('activity/video/finish-condition.html.twig', array());
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

}