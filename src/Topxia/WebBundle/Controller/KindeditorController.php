<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class KindeditorController extends BaseController
{

	public function uploadAction(Request $request)
	{

		try {
			$group = $request->request->get('group');

			$file = $request->files->get('file');
			$record = $this->getFileService()->uploadFile($group, $file);

			$response = array(
		    	'error' => 0,
		    	'url' => $this->get('topxia.twig.web_extension')->getFilePath($record['uri'])
	    	);
			
		} catch (\Exception $e) {
			$response = array(
		    	'error' => 1,
		    	'message' => '文件上传失败！'
	    	);
		}

    	return new Response(json_encode($response));
	}

    private function getFileService()
    {
        return $this->getServiceKernel()->createService('Content.FileService');
    }

}