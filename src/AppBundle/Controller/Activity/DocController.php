<?php

namespace AppBundle\Controller\Activity;

use Biz\File\Service\FileImplementor;
use Biz\File\Service\UploadFileService;
use Biz\Activity\Service\ActivityService;
use Symfony\Component\HttpFoundation\Request;
use Biz\MaterialLib\Service\MaterialLibService;

class DocController extends BaseActivityController implements ActivityActionInterface
{
    public function showAction(Request $request, $activity)
    {
        if (empty($activity)) {
            throw $this->createNotFoundException('activity not found');
        }

        $doc = $this->getActivityService()->getActivityConfig('doc')->get($activity['mediaId']);

        $ssl = $request->isSecure() ? true : false;
        list($result, $error) = $this->getDocFilePlayer($doc, $ssl);

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
            throw $this->createNotFoundException('activity not found');
        }

        $doc = $this->getActivityService()->getActivityConfig('doc')->get($activity['mediaId']);
        $ssl = $request->isSecure() ? true : false;
        list($result, $error) = $this->getDocFilePlayer($doc, $ssl);

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
     * @param  $doc
     *
     * @return array result and error tuple
     */
    protected function getDocFilePlayer($doc, $ssl)
    {
        $file = $this->getUploadFileService()->getFullFile($doc['mediaId']);

        if (empty($file) || empty($file['globalId'])) {
            $error = array('code' => 'error', 'message' => '抱歉，文档文件不存在，暂时无法学习。');

            return array(array(), $error);
        }

        if ($file['type'] != 'document') {
            throw $this->createAccessDeniedException('file type error, expect document');
        }

        $result = $this->getMaterialLibService()->player($file['globalId'], $ssl);

        $isConvertNotSuccess = isset($file['convertStatus']) && $file['convertStatus'] != FileImplementor::CONVERT_STATUS_SUCCESS;

        if ($isConvertNotSuccess) {
            if ($file['convertStatus'] == FileImplementor::CONVERT_STATUS_ERROR) {
                $message = '文档转换失败，请到课程文件管理中，重新转换。';
                $error = array('code' => 'error', 'message' => $message);
            } else {
                $error = array('code' => 'processing', 'message' => '文档还在转换中，还不能查看，请稍等。');
            }
        } else {
            $error = array();
        }

        return array($result, $error);
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
