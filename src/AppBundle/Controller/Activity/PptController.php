<?php

namespace AppBundle\Controller\Activity;

use Biz\Activity\ActivityException;
use Biz\File\Service\UploadFileService;
use Biz\Activity\Service\ActivityService;
use Biz\Player\Service\PlayerService;
use Symfony\Component\HttpFoundation\Request;
use Biz\MaterialLib\Service\MaterialLibService;

class PptController extends BaseActivityController implements ActivityActionInterface
{
    public function showAction(Request $request, $activity)
    {
        $config = $this->getActivityService()->getActivityConfig('ppt');

        $ppt = $config->get($activity['mediaId']);
        $ssl = $request->isSecure() ? true : false;
        list($result, $error) = $this->getPlayerService()->getPptFilePlayer($ppt, $ssl);

        $slides = isset($result['images']) ? $result['images'] : array();

        return $this->render('activity/ppt/show.html.twig', array(
            'ppt' => $ppt,
            'slides' => empty($slides) ? array() : $slides,
            'error' => $error,
            'courseId' => $activity['fromCourseId'],
        ));
    }

    public function getPptTokenAction(Request $request, $mediaId)
    {
        $config = $this->getActivityService()->getActivityConfig('ppt');

        $ppt = $config->get($mediaId);
        $ssl = $request->isSecure() ? true : false;
        list($result, $error) = $this->getPlayerService()->getPptFilePlayer($ppt, $ssl);

        return $this->createJsonResponse(array('result' => $result, 'error' => $error));
    }

    public function previewAction(Request $request, $task)
    {
        $activity = $this->getActivityService()->getActivity($task['activityId']);

        if (empty($activity)) {
            $this->createNewException(ActivityException::NOTFOUND_ACTIVITY());
        }

        $config = $this->getActivityService()->getActivityConfig('ppt');

        $ppt = $config->get($activity['mediaId']);
        $ssl = $request->isSecure() ? true : false;
        list($result, $error) = $this->getPlayerService()->getPptFilePlayer($ppt, $ssl);

        $slides = isset($result['images']) ? $result['images'] : array();

        return $this->render('activity/ppt/preview.html.twig', array(
            'ppt' => $ppt,
            'slides' => $slides,
            'error' => $error,
            'courseId' => $task['courseId'],
        ));
    }

    public function editAction(Request $request, $id, $courseId)
    {
        $activity = $this->getActivityService()->getActivity($id);
        $config = $this->getActivityService()->getActivityConfig('ppt');
        $ppt = $config->get($activity['mediaId']);

        $file = $this->getUploadFileService()->getFile($ppt['mediaId']);

        $ppt['media'] = $file;

        return $this->render('activity/ppt/edit-modal.html.twig', array(
            'activity' => $activity,
            'courseId' => $courseId,
            'ppt' => $ppt,
        ));
    }

    public function createAction(Request $request, $courseId)
    {
        return $this->render('activity/ppt/edit-modal.html.twig', array(
            'courseId' => $courseId,
        ));
    }

    public function finishConditionAction(Request $request, $activity)
    {
        $media = $this->getActivityService()->getActivityConfig('ppt')->get($activity['mediaId']);

        return $this->render('activity/ppt/finish-condition.html.twig', array(
            'media' => $media,
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

    /**
     * @return PlayerService
     */
    protected function getPlayerService()
    {
        return $this->createService('Player:PlayerService');
    }
}
