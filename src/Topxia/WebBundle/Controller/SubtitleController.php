<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Exception\InvalidArgumentException;
use Topxia\Common\Exception\ResourceNotFoundException;

class SubtitleController extends BaseController
{
    public function manageAction(Request $request, $mediaId)
    {
        if (!$this->getUploadFileService()->canManageFile($mediaId)) {
            throw $this->createAccessDeniedException($this->trans('没有权限管理资源'));
        }

        $ssl = $request->isSecure() ? true : false;

        $subtitles = $this->getSubtitleService()->findSubtitlesByMediaId($mediaId, $ssl);

        $media   = $this->getUploadFileService()->getFile($mediaId);
        if (empty($media) || !in_array($media['type'], array('video', 'audio'))) {
            throw new ResourceNotFoundException('uploadFile', $mediaId);
        }
        
        return $this->render('TopxiaWebBundle:MediaManage/Subtitle:manage.html.twig', array(
            'media'  => $media,
            'goto' => $request->query->get('goto'),
            'subtitles' => $subtitles
        ));
    }

    /**
     * 获取某一视频下所有的字幕
     */
    public function listAction(Request $request, $mediaId)
    {
        if (!$this->getUploadFileService()->canManageFile($mediaId)) {
            throw $this->createAccessDeniedException($this->trans('没有权限管理资源'));
        }

        $ssl = $request->isSecure() ? true : false;

        $subtitles = $this->getSubtitleService()->findSubtitlesByMediaId($mediaId, $ssl);
        
        return $this->createJsonResponse(array(
            'subtitles' => $subtitles
        ));
    }

    public function createAction(Request $request, $mediaId)
    {
        if (!$this->getUploadFileService()->canManageFile($mediaId)) {
            throw $this->createAccessDeniedException($this->trans('没有权限管理资源'));
        }

        $fileds = $request->request->all();

        $subtitle = $this->getSubtitleService()->addSubtitle($fileds);

        return $this->createJsonResponse($subtitle);
    }

    public function deleteAction($mediaId, $id)
    {
        if (!$this->getUploadFileService()->canManageFile($mediaId)) {
            throw $this->createAccessDeniedException($this->trans('没有权限管理资源'));
        }

        $this->getSubtitleService()->deleteSubtitle($id);

        return $this->createJsonResponse(true);
    }

    public function previewAction($mediaId)
    {
        return $this->render('TopxiaWebBundle:MediaManage:preview.html.twig', array(
            'mediaId' => $mediaId,
            'context' => array(
                'hideQuestion' => 1,
                'hideBeginning' => true
            )
        ));
    }

    public function manageDialogAction(Request $request)
    {
        $mediaId = $request->query->get('mediaId');
        if (!$this->getUploadFileService()->canManageFile($mediaId)) {
            throw $this->createAccessDeniedException($this->trans('没有权限管理资源'));
        }

        $ssl = $request->isSecure() ? true : false;

        $subtitles = $this->getSubtitleService()->findSubtitlesByMediaId($mediaId, $ssl);
        $media   = $this->getUploadFileService()->getFile($mediaId);
        return $this->render('TopxiaWebBundle:MediaManage/Subtitle:dialog.html.twig', array(
            'subtitles' => $subtitles,
            'media' => $media
        ));
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getUploadFileService()
    {
        return $this->createService('File.UploadFileService');
    }

    protected function getSubtitleService()
    {
        return $this->createService('Subtitle.SubtitleService');
    }
}
