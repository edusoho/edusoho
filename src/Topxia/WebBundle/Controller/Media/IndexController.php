<?php
namespace Topxia\WebBundle\Controller\Media;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Exception\InvalidArgumentException;
use Topxia\Common\Exception\ResourceNotFoundException;
use Topxia\WebBundle\Controller\BaseController;

class IndexController extends BaseController
{
    public function playAction($mediaId, $type)
    {
        if (!$this->getUploadFileService()->canManageFile($mediaId)) {
            throw $this->createAccessDeniedException($this->trans('没有权限管理资源'));
        }
        if ($type == 'preview') {
            $context = array('hideQuestion' => 1);
        } else {
            $context = array('hideQuestion' => 1, 'hideBeginning' => true);
        }

        return $this->forward('TopxiaWebBundle:Player:show', array('id' => $mediaId, 'context' => $context));
    }

    public function previewAction($mediaId)
    {
        return $this->render('TopxiaWebBundle:MediaManage:preview.html.twig', array(
            'mediaId' => $mediaId
        ));
    }

    protected function getUploadFileService()
    {
        return $this->createService('File.UploadFileService');
    }
}
