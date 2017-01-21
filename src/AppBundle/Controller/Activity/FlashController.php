<?php


namespace AppBundle\Controller\Activity;

use AppBundle\Controller\BaseController;
use Biz\Activity\Service\ActivityService;
use Biz\CloudPlatform\CloudAPIFactory;
use Biz\File\Service\UploadFileService;
use Biz\MaterialLib\Service\MaterialLibService;
use Symfony\Component\HttpFoundation\Request;

class FlashController extends BaseController implements ActivityActionInterface
{
    public function showAction(Request $request, $activity)
    {
        $flash             = $this->getActivityService()->getActivityConfig('flash')->get($activity['mediaId']);
        $file              = $this->getUploadFileService()->getFullFile($flash['mediaId']);
        $result            = $this->getMaterialLibService()->player($file['globalId']);
        $flashMedia['uri'] = $result['url'];
        return $this->render('activity/flash/index.html.twig', array(
            'flash'      => $flash,
            'flashMedia' => $flashMedia
        ));
    }

    public function previewAction(Request $request, $task)
    {
        $activity          = $this->getActivityService()->getActivity($task['activityId'], $fetchMedia = true);
        $flash             = $this->getActivityService()->getActivityConfig('flash')->get($activity['mediaId']);
        $file              = $this->getUploadFileService()->getFullFile($flash['mediaId']);
        $result            = $this->getMaterialLibService()->player($file['globalId']);
        $flashMedia['uri'] = $result['url'];

        return $this->render('activity/flash/preview.html.twig', array(
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

    /**
     * @return MaterialLibService
     */
    protected function getMaterialLibService()
    {
        return $this->createService('MaterialLib:MaterialLibService');
    }
}