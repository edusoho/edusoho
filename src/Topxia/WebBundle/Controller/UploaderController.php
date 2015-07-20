<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\WebBundle\Util\UploadToken;
use Topxia\Common\FileToolkit;
use Topxia\Common\ArrayToolkit;
use Topxia\WebBundle\Util\UploaderToken;

/**
 * 素材库上传组件控制器
 */
class UploaderController extends BaseController
{

    public function initAction(Request $request)
    {
        $token = $request->query->get('token');
        $parser = new UploaderToken();
        $params = $parser->parse($token);
        if (!$params) {
        	return $this->createJsonResponse(array('error' => '上传授权码不正确，请重试！'));
        }

        $params = array_merge($request->request->all(), $params);

        $result = $this->getUploadFileService()->initUpload($params);

        return $this->createJsonResponse($result);
    }

    public function finishedAction(Request $request)
    {
        $fileId = $request->request->get('fileId');
        $this->getUploadFileService()->finishedUpload($fileId);
        return $this->createJsonResponse(true);
    }

    public function batchUploadAction(Request $request)
    {
        $targetId = $request->query->get('targetId');
        $targetType = $request->query->get('targetType');
        $token = $request->query->get('token');

        return $this->render('TopxiaWebBundle:Uploader:batch-upload-modal.html.twig', array(
            'targetType' => $targetType,
            'targetId'=> $targetId,
            'token' => $token
        ));

        // $availableExts = array(
        //     'courselesson' => '*.mp3;*.mp4;*.avi;*.flv;*.wmv;*.mov;*.ppt;*.pptx;*.doc;*.docx;*.pdf;*.swf',
        //     'materiallib' => '*',
        //     'coursematerial' => '*',
        // );
    }

    public function echoAction(Request $request)
    {
        $this->getUploadFileService()->finishedUpload(1);

        exit();
    }

    protected function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File.UploadFileService2');
    }

}

