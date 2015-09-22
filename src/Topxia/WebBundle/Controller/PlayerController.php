<?php
namespace Topxia\WebBundle\Controller;

use Topxia\Service\Util\CloudClientFactory;
use Symfony\Component\Yaml\Yaml;

class PlayerController extends BaseController
{
	public function showAction($id, $url)
	{
		$blacklistPath = $this->getServiceKernel()->getParameter('kernel.root_dir') . '/config/play_video_agent_blacklist.yml';
		$blacklist = Yaml::parse(file_get_contents($blacklistPath));
		var_dump($blacklist);

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