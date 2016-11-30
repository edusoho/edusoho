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
        $activity = $this->getActivityService()->getActivity($id);
        $doc      = $this->getActivityService()->getActivityConfig('doc')->get($activity['mediaId']);

        $file = $this->getUploadFileService()->getFullFile($doc['mediaId']);

        if (empty($file)) {
            throw $this->createNotFoundException();
        }

        if (empty($file['globalId'])) {
            throw $this->createNotFoundException();
        }

        if($file['type'] != 'document'){
            throw $this->createAccessDeniedException('file type error, expect document');
        }

        if (isset($file['convertStatus']) && $file['convertStatus'] != 'success') {
            if ($file['convertStatus'] == 'error') {
                $url     = $this->generateUrl('course_manage_files', array('id' => $courseId));
                $message = sprintf('文档转换失败，请到课程<a href="%s" target="_blank">文件管理</a>中，重新转换。', $url);

                $error = array(
                    'error' => array('code' => 'error', 'message' => $message)
                );
            } else {
                $error = array(
                    'error' => array('code' => 'processing', 'message' => '文档还在转换中，还不能查看，请稍等。')
                );
            }
        } else {
            $error = array();
        }

        $result = $this->getMaterialLibService()->player($file['globalId']);

        return $this->render('WebBundle:DocActivity:show.html.twig', array(
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

    protected function getMaterialLibService()
    {
        return ServiceKernel::instance()->createService('MaterialLib:MaterialLib.MaterialLibService');
    }
}