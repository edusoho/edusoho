<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CKEditorController extends BaseController
{

	public function uploadAction(Request $request)
	{
		$funcNum = $request->query->get('CKEditorFuncNum');
		$group = $request->query->get('group');

		$file = $request->files->get('upload');
		$record = $this->getFileService()->uploadFile($group, $file);

	    $url = $this->get('topxia.twig.web_extension')->getFilePath($record['uri']);
	    $message = '';

	    return new Response("<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction({$funcNum}, '{$url}', '{$message}');</script>");

	}

    private function getFileService()
    {
        return $this->getServiceKernel()->createService('Content.FileService');
    }

}