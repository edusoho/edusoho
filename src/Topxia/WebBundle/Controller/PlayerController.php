<?php
namespace Topxia\WebBundle\Controller;


class PlayerController extends BaseController
{
	public function showAction($id)
	{

		$file = $this->getUploadFileService()->getFile($id);
		if(empty($file)){
			throw $this->createNotFoundException();
		}


		return $this->render('TopxiaWebBundle:Player:show.html.twig', array(
			'file' => $file
        ));
	}

	protected function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File.UploadFileService');
    }
}