<?php


namespace AppBundle\Controller\Activity;


use AppBundle\Controller\BaseController;
use Biz\Activity\Service\ActivityService;
use Biz\File\Service\UploadFileService;
use MaterialLib\Service\MaterialLib\MaterialLibService;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\ServiceKernel;

class PptController extends BaseController implements ActivityActionInterface
{
    public function showAction(Request $request, $id, $courseId)
    {
        $activity = $this->getActivityService()->getActivity($id);

        $config = $this->getActivityService()->getActivityConfig('ppt');

        $ppt = $config->get($activity['mediaId']);

        $file  = $this->getUploadFileService()->getFullFile($ppt['mediaId']);

        if(empty($file) || $file['type'] !== 'ppt'){
            throw $this->createAccessDeniedException('file type error');
        }

        $error = array();
        if (isset($file['convertStatus']) && $file['convertStatus'] != 'success') {
            if ($file['convertStatus'] == 'error') {
                $url              = $this->generateUrl('course_manage_files', array('id' => $courseId));
                $message          = sprintf('PPT文档转换失败，请到课程<a href="%s" target="_blank">文件管理</a>中，重新转换。', $url);
                $error['code']    = 'error';
                $error['message'] = $message;
            } else {
                $error['code']    = 'processing';
                $error['message'] = 'PPT文档还在转换中，还不能查看，请稍等。';
            }
        }

        $result = $this->getMaterialLibService()->player($file['globalId']);

        if(isset($result['error'])){
            $error['code'] = 'error';
            $error['message'] = $result['error'];
        }

        $slides = isset($result['images']) ? $result['images'] : array();

        return $this->render('activity/ppt/show.html.twig', array(
            'ppt'      => $ppt,
            'slides'   => $slides,
            'error'    => $error,
            'courseId' => $courseId,
        ));
    }


    public function previewAction(Request $request, $task)
    {
        $activity = $this->getActivityService()->getActivity($task['activityId']);

        if (empty($activity)) {
            throw $this->createNotFoundException('activity not found');
        }

        $config = $this->getActivityService()->getActivityConfig('ppt');

        $ppt = $config->get($activity['mediaId']);

        $file  = $this->getUploadFileService()->getFullFile($ppt['mediaId']);

        if(empty($file) || $file['type'] !== 'ppt'){
            throw $this->createAccessDeniedException('file type error');
        }

        $error = array();
        if (isset($file['convertStatus']) && $file['convertStatus'] != 'success') {
            if ($file['convertStatus'] == 'error') {
                $url              = $this->generateUrl('course_manage_files', array('id' => $courseId));
                $message          = sprintf('PPT文档转换失败，请到课程<a href="%s" target="_blank">文件管理</a>中，重新转换。', $url);
                $error['code']    = 'error';
                $error['message'] = $message;
            } else {
                $error['code']    = 'processing';
                $error['message'] = 'PPT文档还在转换中，还不能查看，请稍等。';
            }
        }

        $result = $this->getMaterialLibService()->player($file['globalId']);

        if(isset($result['error'])){
            $error['code'] = 'error';
            $error['message'] = $result['error'];
        }

        $slides = isset($result['images']) ? $result['images'] : array();
        return $this->render('activity/ppt/preview.html.twig', array(
            'ppt'      => $ppt,
            'slides'   => $slides,
            'error'    => $error,
            'courseId' => $task['courseId'],
        ));
    }

    public function editAction(Request $request, $id, $courseId)
    {
        $activity = $this->getActivityService()->getActivity($id);
        $config   = $this->getActivityService()->getActivityConfig('ppt');
        $ppt      = $config->get($activity['mediaId']);

        $file                             = $this->getUploadFileService()->getFile($ppt['mediaId']);

        $ppt['media'] = $file;

        return $this->render('activity/ppt/edit-modal.html.twig', array(
            'activity' => $activity,
            'courseId' => $courseId,
            'ppt'      => $ppt
        ));
    }

    public function createAction(Request $request, $courseId)
    {
        return $this->render('activity/ppt/edit-modal.html.twig', array(
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