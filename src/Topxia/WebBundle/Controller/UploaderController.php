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

        $params['uploadCallback'] = $this->generateUrl('uploader_upload_callback', array(), true);
        $params['processCallback'] = $this->generateUrl('uploader_process_callback', array(), true);

        $result = $this->getUploadFileService()->initUpload($params);

        return $this->createJsonResponse($result);
    }

    public function finishedAction(Request $request)
    {
        $params = $request->request->all();
        $this->getUploadFileService()->finishedUpload($params);
        return $this->createJsonResponse(true);
    }

    public function uploadCallbackAction(Request $request)
    {
        $params = $request->request->all();
        return $this->createJsonResponse(true);
    }

    public function processCallbackAction(Request $request)
    {
        $params = $request->request->all();

        $this->getUploadFileService()->setFileProcessed($params);

        return $this->createJsonResponse(true);
    }

    public function batchUploadAction(Request $request)
    {
        $token = $request->query->get('token');
        $parser = new UploaderToken();
        $params = $parser->parse($token);
        if (!$params) {
            return $this->createJsonResponse(array('error' => '上传授权码不正确，请重试！'));
        }

        return $this->render('TopxiaWebBundle:Uploader:batch-upload-modal.html.twig', array(
            'token' => $token,
            'targetType' => $params['targetType'],
        ));
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

