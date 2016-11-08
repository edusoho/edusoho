<?php


namespace WebBundle\Controller;


use Biz\Activity\Service\ActivityService;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\File\UploadFileService;

class DocActivityController extends BaseController implements ActivityActionInterface
{
    public function showAction(Request $request, $id, $courseId)
    {
        // TODO: Implement showAction() method.
    }

    public function editAction(Request $request, $id, $courseId)
    {
        $activity = $this->getActivityService()->getActivity($id);
        $config   = $this->getActivityService()->getActivityConfig('doc');
        $doc      = $config->get($activity['mediaId']);

        $file                             = $this->getUploadFileService()->getFile($doc['mediaId']);
        $activity['ext']['media']['name'] = $file['filename'];
        $activity['ext']['media']['id']   = $file['id'];
        return $this->render('WebBundle:DocActivity:edit-modal.html.twig', array(
            'activity' => $activity,
            'courseId' => $courseId,
            'doc'      => $doc
        ));
    }

    public function createAction(Request $request, $courseId)
    {
        return $this->render('WebBundle:DocActivity:edit-modal.html.twig', array(
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