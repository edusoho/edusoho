<?php
namespace Topxia\WebBundle\Controller\Media;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Exception\InvalidArgumentException;
use Topxia\Common\Exception\ResourceNotFoundException;
use Topxia\WebBundle\Controller\BaseController;

class IndexController extends BaseController
{
    public function playAction(Request $request, $mediaId)
    {
        $context = $request->query->get('context');

        if (!$this->getUploadFileService()->canManageFile($mediaId)) {
            throw $this->createAccessDeniedException($this->trans('没有权限管理资源'));
        }

        return $this->forward('TopxiaWebBundle:Player:show', array('id' => $mediaId, 'context' => $context));
    }

    protected function getUploadFileService()
    {
        return $this->createService('File.UploadFileService');
    }
}
