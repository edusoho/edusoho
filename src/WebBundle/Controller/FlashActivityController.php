<?php


namespace WebBundle\Controller;

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;

class FlashActivityController extends BaseController implements ActivityActionInterface
{
    public function showAction(Request $request, $id, $courseId)
    {

    }

    public function editAction(Request $request, $id, $courseId)
    {
        $activity = $this->getActivityService()->getActivity($id);
        $config   = $this->getActivityService()->getActivityConfig('flash');
        $flash    = $config->get($activity['mediaId']);

        $file     = $this->getUploadFileService()->getFile($flash['mediaId']);

        $activity['ext']['media']['name'] = $file['filename'];
        $activity['ext']['media']['id']   = $file['id'];

        return $this->render('WebBundle:FlashActivity:edit-modal.html.twig', array(
            'activity' => $activity,
            'courseId'  => $courseId,
            'flash'     => $flash
        ));
    }

    public function createAction(Request $request, $courseId)
    {

        return $this->render('WebBundle:FlashActivity:edit-modal.html.twig', array(
            'courseId' => $courseId
        ));
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return ServiceKernel::instance()->createService('File.UploadFileService');
    }

}