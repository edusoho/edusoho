<?php


namespace AppBundle\Controller\Activity;


use AppBundle\Controller\BaseController;
use Biz\Activity\Service\ActivityService;
use Biz\File\Service\UploadFileService;
use Biz\MaterialLib\Service\MaterialLibService;
use Symfony\Component\HttpFoundation\Request;

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

        if ($isConvertNotSuccess) {
            if ($file['convertStatus'] == 'error' && $isPrivate) {
                $message = sprintf('文档转换失败，请联系老师，重新转换。');
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