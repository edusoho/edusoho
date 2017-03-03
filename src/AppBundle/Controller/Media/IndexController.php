<?php

namespace AppBundle\Controller\Media;

use AppBundle\Controller\BaseController;
use Biz\File\Service\UploadFileService;
use Symfony\Component\HttpFoundation\Request;

class IndexController extends BaseController
{
    public function playAction(Request $request, $mediaId)
    {
        $context = $request->query->get('context');

        if (!$this->getUploadFileService()->canManageFile($mediaId)) {
            throw $this->createAccessDeniedException('没有权限管理资源');
        }

        return $this->forward('AppBundle:Player:show', array('id' => $mediaId, 'context' => $context));
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->getBiz()->service('File:UploadFileService');
    }
}
