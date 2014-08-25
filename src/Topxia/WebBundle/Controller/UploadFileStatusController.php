<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class UploadFileStatusController extends BaseController
{
	public function setAction(Request $request)
    {
    	$fields = $request->request->all();
    	$uploadFileStatus = $this->getUploadFileStatusService()->setUploadFileStatus($fields);
    	return $this->createJsonResponse($uploadFileStatus);
    }

    public function getByKeyAction(Request $request)
    {
    	$uploadFileStatus = $this->getUploadFileStatusService()->getUploadFileStatusByKey($request->query->get('scopKey'));
    	return $this->createJsonResponse($uploadFileStatus);
    }

    public function deleteByKeyAction(Request $request)
    {
    	$this->getUploadFileStatusService()->deleteUploadFileStatus($request->query->get('scopKey'));
    	return $this->createJsonResponse(true);
    }

    private function getUploadFileStatusService()
	{
		return $this->getServiceKernel()->createService('File.UploadFileStatusService');
	}
}