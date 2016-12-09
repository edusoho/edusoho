<?php


namespace AppBundle\Controller\Activity;

use AppBundle\Controller\BaseController;
use Biz\Activity\Service\ActivityService;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\CloudPlatform\CloudAPIFactory;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\File\UploadFileService;

class FlashActivityController extends BaseController implements ActivityActionInterface
{
    public function showAction(Request $request, $id, $courseId)
    {
        $activity = $this->getActivityService()->getActivity($id);
        $flash    = $this->getActivityService()->getActivityConfig('flash')->get($activity['mediaId']);

        $file = $this->getUploadFileService()->getFullFile($flash['mediaId']);

        $apiClient              = CloudAPIFactory::create('leaf');
        $result                 = $apiClient->get(sprintf('/resources/%s/player', $file['globalId']));
        $flashMedia['uri'] = $result['url'];

        return $this->render('WebBundle:FlashActivity:index.html.twig', array(
            'flash'      => $flash,
            'flashMedia' => $flashMedia
        ));
    }

    public function editAction(Request $request, $id, $courseId)
    {
        $activity = $this->getActivityService()->getActivity($id);
        $config   = $this->getActivityService()->getActivityConfig('flash');
        $flash    = $config->get($activity['mediaId']);

        $file = $this->getUploadFileService()->getFile($flash['mediaId']);

        $flash['media'] = $file;

        return $this->render('WebBundle:FlashActivity:edit-modal.html.twig', array(
            'activity' => $activity,
            'courseId' => $courseId,
            'flash'    => $flash
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