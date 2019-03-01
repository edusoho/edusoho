<?php

namespace AppBundle\Controller\Activity;

use Biz\Activity\ActivityException;
use Biz\File\Service\UploadFileService;
use Biz\Activity\Service\ActivityService;
use Biz\Player\Service\PlayerService;
use Symfony\Component\HttpFoundation\Request;
use Biz\MaterialLib\Service\MaterialLibService;

class DocController extends BaseActivityController implements ActivityActionInterface
{
    public function showAction(Request $request, $activity)
    {
        if (empty($activity)) {
            $this->createNewException(ActivityException::NOTFOUND_ACTIVITY());
        }

        $doc = $this->getActivityService()->getActivityConfig('doc')->get($activity['mediaId']);

        $ssl = $request->isSecure() ? true : false;
        list($result, $error) = $this->getPlayerService()->getDocFilePlayer($doc, $ssl);

        return $this->render('activity/new-doc/show.html.twig', array(
            'doc' => $doc,
            'error' => $error,
            'docMedia' => $result,
        ));
    }

    public function previewAction(Request $request, $task)
    {
        $activity = $this->getActivityService()->getActivity($task['activityId']);

        if (empty($activity)) {
            $this->createNewException(ActivityException::NOTFOUND_ACTIVITY());
        }

        $doc = $this->getActivityService()->getActivityConfig('doc')->get($activity['mediaId']);

        $ssl = $request->isSecure() ? true : false;
        list($result, $error) = $this->getPlayerService()->getDocFilePlayer($doc, $ssl);

        return $this->render('activity/new-doc/preview.html.twig', array(
            'doc' => $doc,
            'error' => $error,
            'docMedia' => $result,
        ));
    }

    public function editAction(Request $request, $id, $courseId)
    {
        $activity = $this->getActivityService()->getActivity($id);
        $config = $this->getActivityService()->getActivityConfig('doc');
        $doc = $config->get($activity['mediaId']);

        $file = $this->getUploadFileService()->getFile($doc['mediaId']);
        $doc['media'] = $file;

        return $this->render('activity/doc/edit-modal.html.twig', array(
            'activity' => $activity,
            'courseId' => $courseId,
            'doc' => $doc,
        ));
    }

    public function createAction(Request $request, $courseId)
    {
        return $this->render('activity/doc/edit-modal.html.twig', array(
            'courseId' => $courseId,
        ));
    }

    public function finishConditionAction(Request $request, $activity)
    {
        $media = $this->getActivityService()->getActivityConfig('doc')->get($activity['mediaId']);

        return $this->render('activity/doc/finish-condition.html.twig', array(
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
