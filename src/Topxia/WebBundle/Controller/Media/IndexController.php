<?php
namespace Topxia\WebBundle\Controller\Media;

use Biz\File\Service\UploadFileService;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\ServiceKernel;
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

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return ServiceKernel::instance()->createService('File:UploadFileService');
    }
}
