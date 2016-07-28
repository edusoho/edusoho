<?php
namespace Topxia\WebBundle\Controller;


use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Util\UploaderToken;

class AttachmentController extends BaseController
{
    public function uploadAction(Request $request)
    {
        $token  = $request->query->get('token');
        $parser = new UploaderToken();
        $params = $parser->parse($token);

        if (!$params) {
            return $this->createJsonResponse(array('error' => '上传授权码不正确，请重试！'));
        }

        return $this->render('TopxiaWebBundle:Attachment:upload-modal.html.twig', array(
            'token'      => $token,
            'targetType' => $params['targetType']
        ));
    }

    public function formFieldsAction(Request $request, $targetType, $targetId)
    {
        return $this->render('TopxiaWebBundle:Attachment:form-fields.html.twig', array(
            'targetType'  => $targetType,
            'attachments' => $this->getAttachmentService()->findByTargetTypeAndTargetId($targetType, $targetId)
        ));
    }

    public function listAction(Request $request, $targetType, $targetId)
    {
        return $this->render('TopxiaWebBundle:Attachment:list.html.twig', array(
            'attachments' => $this->getAttachmentService()->findByTargetTypeAndTargetId($targetType, $targetId)
        ));
    }

    public function previewAction(Request $request, $id)
    {
        $attachment = $this->getAttachmentService()->get($id);
        $file = $this->getUploadFileService()->getFile($attachment['fileId']);

        if ($file['storage'] == 'cloud') {
            return $this->forward('TopxiaAdminBundle:CloudFile:preview', array(
                'request'  => $request,
                'globalId' => $file['globalId']
            ));
        }

        return $this->render('MaterialLibBundle:Web:preview.html.twig', array(
            'file' => $file
        ));
    }

    public function downloadAction(Request $request, $id)
    {
        $attachment = $this->getAttachmentService()->get($id);
        $file = $this->getUploadFileService()->getFile($attachment['fileId']);
        return $this->forward('TopxiaWebBundle:UploadFile:download', array(
            'request' => $request,
            'fileId'  => $file['id']
        ));
    }

    public function deleteAction($id){

        $this->getAttachmentService()->delete($id);
        return $this->createJsonResponse(array('msg'=>'ok'));
    }

    protected function getAttachmentService()
    {
        return $this->getServiceKernel()->createService('Attachment.AttachmentService');
    }

    protected function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File.UploadFileService');
    }
}