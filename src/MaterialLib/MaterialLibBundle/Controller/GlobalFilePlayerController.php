<?php

namespace MaterialLib\MaterialLibBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use MaterialLib\MaterialLibBundle\Controller\BaseController;

class GlobalFilePlayerController extends BaseController
{
    public function playerAction(Request $reqeust, $globalId)
    {
        $file = $this->getMaterialLibService()->get($globalId);

        if (empty($file)) {
            throw $this->createNotFoundException();
        }

        if ($file['type'] == 'video') {
            return $this->videoPlayer($file);
        }
    }

    protected function videoPlayer($file)
    {
        $player = "balloon-cloud-video-player";
        $url    = $this->getPlayUrl($globalId, array());

        return $this->render('TopxiaWebBundle:Player:video-player.html.twig', array(
            'file'             => $file,
            'url'              => $url,
            'context'          => array(),
            'player'           => $player,
            'agentInWhiteList' => $this->agentInWhiteList($request->headers->get("user-agent"))
        ));
    }

    protected function getMaterialLibService()
    {
        return $this->createService('MaterialLib:MaterialLib.MaterialLibService');
    }
}
