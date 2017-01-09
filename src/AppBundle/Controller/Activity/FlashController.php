<?php


namespace AppBundle\Controller\Activity;

use AppBundle\Controller\BaseController;
use Biz\Activity\Service\ActivityService;
use Biz\File\Service\UploadFileService;
use Symfony\Component\HttpFoundation\Request;
use Biz\CloudPlatform\CloudAPIFactory;

class FlashController extends BaseController implements ActivityActionInterface
{
    public function showAction(Request $request, $id, $courseId)
    {
        $activity = $this->getActivityService()->getActivity($id);
        $flash    = $this->getActivityService()->getActivityConfig('flash')->get($activity['mediaId']);

        $file = $this->getUploadFileService()->getFullFile($flash['mediaId']);

        $apiClient              = CloudAPIFactory::create('leaf');
        $result                 = $apiClient->get(sprintf('/resources/%s/player', $file['globalId']));
        $flashMedia['uri'] = $result['url'];

        return $this->render('activity/flash/index.html.twig', array(
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

        return $this->render('activity/flash/edit-modal.html.twig', array(
            'activity' => $activity,
            'courseId' => $courseId,
            'flash'    => $flash
        ));
    }

    public function createAction(Request $request, $courseId)
    {

        return $this->render('activity/flash/edit-modal.html.twig', array(
            'courseId' => $courseId
        ));
    }

    public function finishConditionAction($activity)
    {
        $media = $this->getActivityService()->getActivityConfig('flash')->get($activity['mediaId']);

        return $this->render('activity/flash/finish-condition.html.twig', array(
            'media' => $media
        ));
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
    }

}