<?php
namespace WebBundle\Controller;

use Biz\Activity\Service\ActivityService;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\ServiceKernel;

class ActivityController extends BaseController
{
    public function showAction(Request $request, $id, $courseId)
    {
        $activity = $this->getActivityService()->getActivity($id);

        if (empty($activity)) {
            throw $this->createNotFoundException('activity not found');
        }

        $config         = $this->getActivityService()->getActivityConfig($activity['mediaType']);
        $showController = $config->getAction('show');
        return $this->forward($showController, array(
            'id'       => $id,
            'courseId' => $courseId,
        ));
    }

    public function updateAction(Request $request, $id, $courseId)
    {
        $activity       = $this->getActivityService()->getActivity($id);
        $config         = $this->getActivityService()->getActivityConfig($activity['mediaType']);
        $editController = $config->getAction('edit');
        return $this->forward($editController, array(
            'id'       => $activity['id'],
            'courseId' => $courseId
        ));
    }

    public function createAction(Request $request, $type, $courseId)
    {
        $config           = $this->getActivityService()->getActivityConfig($type);
        $createController = $config->getAction('create');

        return $this->forward($createController, array(
            'courseId' => $courseId
        ));
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
        return ServiceKernel::instance()->createService('Course.CourseService');
    }

}
