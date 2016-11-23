<?php
/**
 * User: Edusoho V8
 * Date: 26/10/2016
 * Time: 19:25
 */

namespace WebBundle\Controller;


use Biz\Activity\Service\ActivityService;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\CloudPlatform\Client\CloudAPI;
use Topxia\Service\CloudPlatform\CloudAPIFactory;
use Topxia\Service\Common\ServiceKernel;

class VideoActivityController extends BaseController implements ActivityActionInterface
{
    public function showAction(Request $request, $id,  $courseId)
    {
        $activity = $this->getActivityService()->getActivity($id);
        if ($this->getMediaSource($activity) == 'self') {
            return $this->render('WebBundle:VideoActivity:show.html.twig', array(
                'activity' => $activity,
                'courseId' => $courseId
            ));
        } else {
            return $this->render('WebBundle:VideoActivity:swf-show.html.twig', array(
                'activity' => $activity,
            ));
        }
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
        $task     = $this->tryLearnTask($courseId, $id);
        $activity = $this->getActivityService()->getActivity($id);
        $activity = $this->fillMinuteAndSecond($activity);
        return $this->render('WebBundle:VideoActivity:modal.html.twig', array(
            'activity' => $activity,
            'task'     => $task,
            'courseId' => $courseId
        ));
    }

    public function createAction(Request $request, $courseId)
    {
        return $this->render('WebBundle:VideoActivity:modal.html.twig', array(
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


    protected function tryLearnTask($courseId, $taskId)
    {
        $this->getCourseService()->tryLearnCourse($courseId);
        $task = $this->getTaskService()->getTask($taskId);

        if (empty($task)) {
            throw $this->createResourceNotFoundException('task', $taskId);
        }

        if ($task['courseId'] != $courseId) {
            throw $this->createAccessDeniedException();
        }
        return $task;
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }

    protected function getTaskService()
    {
        return $this->getBiz()->service('Task:TaskService');
    }

    protected function getCourseService()
    {
        return ServiceKernel::instance()->createService('Course.CourseService');
    }

    protected function getUploadFileService()
    {
        return ServiceKernel::instance()->createService('File.UploadFileService');
    }

}