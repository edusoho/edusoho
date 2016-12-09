<?php

namespace AppBundle\Controller\Activity;


use AppBundle\Controller\BaseController;
use Biz\Activity\Service\ActivityService;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\ServiceKernel;

class VideoController extends BaseController implements ActivityActionInterface
{
    public function showAction(Request $request, $id, $courseId)
    {
        $activity = $this->getActivityService()->getActivityFetchMedia($id);
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
        $activity = $this->getActivityService()->getActivityFetchMedia($id);
        $activity = $this->fillMinuteAndSecond($activity);
        return $this->render('WebBundle:VideoActivity:modal.html.twig', array(
            'activity' => $activity,
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

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
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