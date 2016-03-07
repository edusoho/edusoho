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

    protected function getPlayUrl($globalId, $context)
    {
        $file = $this->getMaterialLibService()->get($globalId);

        if (empty($file)) {
            throw $this->createNotFoundException();
        }

        if (!in_array($file["type"], array("audio", "video"))) {
            throw $this->createAccessDeniedException();
        }

        $factory = new CloudClientFactory();
        $client  = $factory->createClient();

        if (!empty($file['metas']) && !empty($file['metas']['levels']['sd']['key'])) {
            // if (isset($file['convertParams']['convertor']) && ($file['convertParams']['convertor'] == 'HLSEncryptedVideo')) {
            $token = $this->makeToken('hls.playlist', $file['id'], $context);

            $params = array(
                'id'    => $file['id'],
                'token' => $token['token']
            );

            return $this->generateUrl('hls_playlist', $params, true);
            // } else {
            //     $result = $client->generateHLSQualitiyListUrl($file['metas'], 3600);
            // }
        } else {
            if (!empty($file['metas']) && !empty($file['metas']['hd']['key'])) {
                $key = $file['metas']['hd']['key'];
            } else {
                $key = $file['reskey'];
            }

            if ($key) {
                $result = $client->generateFileUrl($client->getBucket(), $key, 3600);
            }
        }

        return $result['url'];
    }

    protected function getMaterialLibService()
    {
        return $this->createService('MaterialLib:MaterialLib.MaterialLibService');
    }
}
