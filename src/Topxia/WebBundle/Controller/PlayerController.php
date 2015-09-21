<?php
namespace Topxia\WebBundle\Controller;

use Topxia\Service\Util\CloudClientFactory;

class PlayerController extends BaseController
{
	public function showAction($id, $url)
	{
		$file = $this->getUploadFileService()->getFile($id);
		if(empty($file)){
			throw $this->createNotFoundException();
		}

		if($file["storage"] == 'cloud' && $file["type"] == 'video') {
			if($this->setting("developer.balloon_player", 0)){
		        $player = "balloon-cloud-video-player";
			} else {
		        $player = "cloud-video-player";
			}
		} else if($file["storage"] == 'local' && $file["type"] == 'video'){
	        $player = "local-video-player";
		} else if($file["type"] == 'audio'){
	        $player = "audio-player";
		}

		return $this->render('TopxiaWebBundle:Player:show.html.twig', array(
			'file' => $file,
			'url' => $url,
			'player' => $player
        ));
	}

	protected function getTokenService()
    {
        return $this->getServiceKernel()->createService('User.TokenService');
    }

	protected function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File.UploadFileService');
    }
}