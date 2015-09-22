<?php
namespace Topxia\WebBundle\Controller;

use Topxia\Service\Util\CloudClientFactory;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\HttpFoundation\Request;

class PlayerController extends BaseController
{
	public function showAction(Request $request, $id, $url)
	{
		$file = $this->getUploadFileService()->getFile($id);
		if(empty($file)){
			throw $this->createNotFoundException();
		}

		if($file["storage"] == 'cloud' && $file["type"] == 'video') {
			$blacklistPath = $this->getServiceKernel()->getParameter('kernel.root_dir') . '/config/play_video_agent_blacklist.yml';
			$blacklist = Yaml::parse(file_get_contents($blacklistPath));
			foreach ($blacklist["blacklist"] as $value) {
				if(strpos($request->headers->get("user-agent"), $value)) {
					return $this->render('TopxiaWebBundle:Player:show.html.twig', array());
				}
			}
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