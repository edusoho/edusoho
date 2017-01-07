<?php


namespace AppBundle\Controller\Activity;


use AppBundle\Controller\BaseController;
use Biz\Activity\Service\ActivityService;
use Biz\File\Service\UploadFileService;
use MaterialLib\Service\MaterialLib\MaterialLibService;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\ServiceKernel;

class DocController extends BaseController implements ActivityActionInterface
{
    public function showAction(Request $request, $id, $courseId)
    {
        $activity = $this->getActivityService()->getActivity($id);
        $doc      = $this->getActivityService()->getActivityConfig('doc')->get($activity['mediaId']);

        $file = $this->getUploadFileService()->getFullFile($doc['mediaId']);

        if (empty($file)) {
            throw $this->createNotFoundException();
        }

        if (empty($file['globalId'])) {
            throw $this->createNotFoundException();
        }

        if ($file['type'] != 'document') {
            throw $this->createAccessDeniedException('file type error, expect document');
        }

        $result = $this->getMaterialLibService()->player($file['globalId']);

        $isConvertNotSuccess = isset($file['convertStatus']) && $file['convertStatus'] != 'success';
        $isPrivate = !isset($result['pdf']) && !isset($result['swf']);

        if ($isConvertNotSuccess || $isPrivate) {
            if ($file['convertStatus'] == 'error' || $isPrivate) {
                $url     = $this->generateUrl('course_manage_files', array('id' => $courseId));
                $message = sprintf('文档转换失败，请到课程<a href="%s" target="_blank">文件管理</a>中，重新转换。', $url);
                $error = array('code' => 'error', 'message' => $message);
            } else {
                $error = array('code' => 'processing', 'message' => '文档还在转换中，还不能查看，请稍等。');
            }
        } else {
            $error = array();
        }

        return $this->render('activity/doc/show.html.twig', array(
            'doc'      => $doc,
            'error'    => $error,
            'docMedia' => $result
        ));
    }


    public function previewAction(Request $request, $task)
    {
        $activity = $this->getActivityService()->getActivity($task['activityId']);

        if (empty($activity)) {
            throw $this->createNotFoundException('activity not found');
        }

        $doc      = $this->getActivityService()->getActivityConfig('doc')->get($activity['mediaId']);

        $file = $this->getUploadFileService()->getFullFile($doc['mediaId']);

        if (empty($file)) {
            throw $this->createNotFoundException();
        }

        if (empty($file['globalId'])) {
            throw $this->createNotFoundException();
        }

        if ($file['type'] != 'document') {
            throw $this->createAccessDeniedException('file type error, expect document');
        }

        $result = $this->getMaterialLibService()->player($file['globalId']);

        $isConvertNotSuccess = isset($file['convertStatus']) && $file['convertStatus'] != 'success';
        $isPrivate = !isset($result['pdf']) && !isset($result['swf']);

        if ($isConvertNotSuccess || $isPrivate) {
            if ($file['convertStatus'] == 'error' || $isPrivate) {
                $url     = $this->generateUrl('course_manage_files', array('id' => $task['courseId']));
                $message = sprintf('文档转换失败，请到课程<a href="%s" target="_blank">文件管理</a>中，重新转换。', $url);
                $error = array('code' => 'error', 'message' => $message);
            } else {
                $error = array('code' => 'processing', 'message' => '文档还在转换中，还不能查看，请稍等。');
            }
        } else {
            $error = array();
        }

        return $this->render('activity/doc/preview.html.twig', array(
            'doc'      => $doc,
            'error'    => $error,
            'docMedia' => $result
        ));
    }

    public function editAction(Request $request, $id, $courseId)
    {
        $activity = $this->getActivityService()->getActivity($id);
        $config   = $this->getActivityService()->getActivityConfig('doc');
        $doc      = $config->get($activity['mediaId']);

        $file         = $this->getUploadFileService()->getFile($doc['mediaId']);
        $doc['media'] = $file;
        return $this->render('activity/doc/edit-modal.html.twig', array(
            'activity' => $activity,
            'courseId' => $courseId,
            'doc'      => $doc
        ));
    }

    public function createAction(Request $request, $courseId)
    {
        return $this->render('activity/doc/edit-modal.html.twig', array(
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