<?php

namespace AppBundle\Controller\File;

use AppBundle\Controller\BaseController;
use Biz\File\UploadFileException;
use Biz\User\UserException;
use Topxia\Service\Common\ServiceKernel;
use AppBundle\Util\UploaderToken;
use Symfony\Component\HttpFoundation\Request;

class AttachmentController extends BaseController
{
    public function uploadAction(Request $request)
    {
        $query = $request->query->all();
        $useSeajs = $request->query->get('useSeajs', false);
        $module = $request->query->get('module', '');
        $parser = new UploaderToken();
        $params = $parser->parse($query['token']);

        if (!$params) {
            return $this->createJsonResponse(array('error' => '上传授权码不正确，请重试！'));
        }

        $template = 'attachment/upload-modal.html.twig';
        if ($useSeajs) {
            $template = 'attachment/seajs-upload-modal.html.twig';
        }

        $attachmentSetting = $this->setting('cloud_attachment', array());

        return $this->render($template, array(
            'token' => $query['token'],
            'idsClass' => $query['idsClass'],
            'listClass' => $query['listClass'],
            'module' => $module,
            'targetType' => $params['targetType'],
            'targetId' => $params['targetId'],
            'fileSize' => empty($attachmentSetting['fileSize']) ? 0 : $attachmentSetting['fileSize'],
        ));
    }

    public function formFieldsAction(Request $request, $targetType, $targetId)
    {
        $targets = explode('.', $targetType);
        $type = 'attachment';
        $attachments = $this->getUploadFileService()->findUseFilesByTargetTypeAndTargetIdAndType($targetType, $targetId, $type);

        return $this->render('attachment/form-fields.html.twig', array(
            'target' => array_shift($targets),
            'targetType' => $targetType,
            'fileType' => array_pop($targets),
            'type' => 'attachment',
            'useType' => $request->query->get('useType', false),
            'showLabel' => $request->query->get('showLabel', true),
            'useSeajs' => $request->query->get('useSeajs', false),
            'attachments' => $attachments,
            'currentTarget' => $request->query->get('currentTarget', ''),
        ));
    }

    public function listAction(Request $request, $targetType, $targetId)
    {
        $type = 'attachment';

        return $this->render('attachment/list.html.twig', array(
            'attachments' => $this->getUploadFileService()->findUseFilesByTargetTypeAndTargetIdAndType($targetType, $targetId, $type),
        ));
    }

    public function previewAction(Request $request, $id)
    {
        return $this->render('attachment/preview.html.twig', array(
            'id' => $id,
        ));
    }

    public function directVideoPreviewAction(Request $request, $id)
    {
        $ssl = $request->isSecure() ? true : false;
        $file = $this->getUploadFileService()->getDownloadMetas($id, $ssl);

        return $this->render('attachment/direct-video-preview.html.twig', array(
            'url' => $file['url'],
        ));
    }

    public function playerAction(Request $request, $id)
    {
        $user = $this->getUser();

        if (!$user->isLogin()) {
            $this->createNewException(UserException::UN_LOGIN());
        }

        $attachment = $this->getUploadFileService()->getUseFile($id);
        $file = $this->getUploadFileService()->getFile($attachment['fileId']);

        if ('cloud' != $file['storage']) {
            $this->createNewException(UploadFileException::NOTFOUND_ATTACHMENT());
        }

        if ('attachment' != $file['targetType']) {
            $this->createNewException(UploadFileException::NOTFOUND_ATTACHMENT());
        }

        return $this->forward('AppBundle:MaterialLib/GlobalFilePlayer:player', array(
            'request' => $request,
            'globalId' => $file['globalId'],
        ));
    }

    public function downloadAction(Request $request, $id)
    {
        $user = $this->getUser();
        if (!$user->isLogin()) {
            $this->createNewException(UserException::UN_LOGIN());
        }
        $attachment = $this->getUploadFileService()->getUseFile($id);

        if (empty($attachment)) {
            $this->createNewException(UploadFileException::NOTFOUND_ATTACHMENT());
        }

        if ('attachment' != $attachment['type']) {
            return $this->createMessageResponse('error', '无权下载该资料');
        }

        $file = $this->getUploadFileService()->getFile($attachment['fileId']);

        return $this->forward('AppBundle:UploadFile:download', array(
            'request' => $request,
            'fileId' => $file['id'],
        ));
    }

    public function fileShowAction(Request $request, $fileId)
    {
        $module = $request->query->get('module', '');
        $file = $this->getUploadFileService()->getFile($fileId);
        $attachment = array('file' => $file);

        $template = 'attachment/file-item.html.twig';
        if ('simple' == $module) {
            $template = 'testpaper/subject/file-simple-item.html.twig';
        }

        return $this->render($template, array(
            'attachment' => $attachment,
        ));
    }

    public function deleteAction(Request $request, $id)
    {
        $previewType = $request->query->get('type', 'attachment');
        if ('attachment' == $previewType) {
            $this->getUploadFileService()->deleteUseFile($id);
        } else {
            if ($this->getUploadFileService()->canManageFile($id)) {
                $this->getUploadFileService()->deleteFile($id);
            } else {
                $this->createNewException(UploadFileException::FORBIDDEN_MANAGE_FILE());
            }
        }

        return $this->createJsonResponse(array('msg' => 'ok'));
    }

    protected function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}
