<?php


namespace WebBundle\Controller;


use Biz\Activity\Service\ActivityService;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\File\UploadFileService;

class PptActivityController extends BaseController implements ActivityActionInterface
{
    public function showAction(Request $request, $id, $courseId)
    {
        // TODO: Implement showAction() method.
    }

    public function editAction(Request $request, $id, $courseId)
    {
        $activity = $this->getActivityService()->getActivity($id);
        $config   = $this->getActivityService()->getActivityConfig('ppt');
        $ppt      = $config->get($activity['mediaId']);

        $file                             = $this->getUploadFileService()->getFile($ppt['mediaId']);
        $activity['ext']['media']['name'] = $file['filename'];
        $activity['ext']['media']['id']   = $file['id'];
        return $this->render('WebBundle:PptActivity:edit-modal.html.twig', array(
            'activity' => $activity,
            'courseId' => $courseId,
            'ppt'      => $ppt
        ));
    }

    public function createAction(Request $request, $courseId)
    {
        return $this->render('WebBundle:PptActivity:edit-modal.html.twig', array(
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