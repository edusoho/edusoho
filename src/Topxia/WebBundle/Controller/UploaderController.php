<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\WebBundle\Util\UploadToken;
use Topxia\Common\FileToolkit;
use Topxia\Common\ArrayToolkit;

class UploaderController extends BaseController
{

	public function initAction(Request $request)
	{
		$params = $request->request->all();

		$result = $this->getUploadFileService()->initUpload($params);

		return $this->createJsonResponse($result);
	}

	public function finishedAction(Request $request)
	{
		$fileId = $request->request->get('fileId');
		$this->getUploadFileService()->finishedUpload($fileId);
		return $this->createJsonResponse(true);
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