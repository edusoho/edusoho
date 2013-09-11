<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class KindeditorController extends BaseController
{

	public function uploadAction(Request $request)
	{
		$group = $request->request->get('group');

		$file = $request->files->get('file');
		$record = $this->getFileService()->uploadFile($group, $file);

	    return $this->createJsonResponse(array(
	    	'error' => 0,
	    	'url' => $this->get('topxia.twig.web_extension')->getFilePath($record['uri'])

    	));

	}

    private function getFileService()
    {
        return $this->getServiceKernel()->createService('Content.FileService');
    }

}