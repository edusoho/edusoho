<?php
namespace Topxia\WebBundle\Controller;


class PlayerController extends BaseController
{
	public function showAction($id, $courseId, $lessonId)
	{

		$file = $this->getUploadFileService()->getFile($id);
		if(empty($file)){
			throw $this->createNotFoundException();
		}

		if($file["storage"] == 'cloud' && $file["type"] == 'video') {
			if($this->setting("developer.balloon_player", 0)){
				$url = $this->generateUrl('course_lesson_playlist', array(
		            'courseId' => $courseId,
		            'lessonId' => $lessonId,
		        ), true)."?json=1";
		        $player = "balloon-cloud-video-player";
			} else {
				$token = $this->getTokenService()->makeToken('hls.playlist', array(
		            'data' => $file['id'], 
		            'times' => 3, 
		            'duration' => 3600,
		            'userId' => $this->getCurrentUser()->getId()
		        ));
		        $url = $this->generateUrl('hls_playlist', array(
		            'id' => $file['id'],
		            'token' => $token['token'],
		        ), true);
		        $player = "cloud-video-player";
			}
		} else if($file["storage"] == 'local' && $file["type"] == 'video'){
			$url = $this->generateUrl('course_lesson_media', array(
	            'courseId' => $courseId,
	            'lessonId' => $lessonId,
	        ), true);
	        $player = "local-video-player";
		} else if($file["type"] == 'audio'){
			$url = $this->generateUrl('course_lesson_media', array(
	            'courseId' => $courseId,
	            'lessonId' => $lessonId,
	        ), true);
	        $player = "audio-player";
		}


		return $this->render('TopxiaWebBundle:Player:show.html.twig', array(
			'file' => $file,
			'courseId' => $courseId, 
			'lessonId' => $lessonId,
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