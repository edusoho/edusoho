<?php
namespace Mooc\WebBundle\Controller;

use Topxia\Common\FileToolkit;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Topxia\WebBundle\Controller\PlayerController as BasePlayerController;

class PlayerController extends BasePlayerController
{
    public function localMediaAction(Request $request, $id, $token)
    {
        $file = $this->getUploadFileService()->getFile($id);

        if (empty($file)) {
            throw $this->createNotFoundException();
        }

        if (!in_array($file["type"], array("audio", "video"))) {
            throw $this->createAccessDeniedException();
        }

        $token = $this->getTokenService()->verifyToken('local.media', $token);
        $user  = $this->getCurrentUser();

        if ($token['userId'] != $user->getId()) {
            if (!$this->getUserService()->hasAdminRoles($user->getId())) {
                throw $this->createAccessDeniedException();
            }
        }

        $response = BinaryFileResponse::create($file['fullpath'], 200, array(), false);
        $response->trustXSendfileTypeHeader();

        $mimeType = FileToolkit::getMimeTypeByExtension($file['ext']);

        if ($mimeType) {
            $response->headers->set('Content-Type', $mimeType);
        }

        return $response;
    }
}
