<?php

namespace AppBundle\Controller;


use Biz\File\Service\UploadFileService;
use MaterialLib\Service\MaterialLib\MaterialLibService;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\Util\CloudClientFactory;

class PlayerController extends BaseController
{
    public function showAction(Request $request, $id, $context = array())
    {
        $file = $this->getUploadFileService()->getFullFile($id);
        if (empty($file)) {
            throw $this->createNotFoundException('file not found');
        }
        if (!in_array($file['type'], array('audio', 'video'))) {
            throw $this->createAccessDeniedException("player does not support  file type: {$file['type']}");
        }

        $player = $this->getPlayer($file);

        $agentInWhiteList = $this->agentInWhiteList($request->headers->get('user-agent'));

        if ($file['type'] == 'video' && $file['storage'] == 'cloud') {
            if (!$this->isHiddenVideoHeader()) {
                $videoHeaderFile = $this->getUploadFileService()->getFileByTargetType('headLeader');
                if (!empty($videoHeaderFile) && $videoHeaderFile['convertStatus'] == "success") {
                    $context["videoHeaderLength"] = $videoHeaderFile["length"];
                }
            }

            if (!empty($file['convertParams']['hasVideoWatermark'])) {
                $file['videoWatermarkEmbedded'] = 1;
            }

            $result = $this->getMaterialLibService()->player($file['globalId']);
            if ($agentInWhiteList) {
                $context['hideQuestion'] = 1;
                if (isset($file['mcStatus']) && $file['mcStatus'] == 'yes') {
                    $player = "local-video-player";
                    $mp4Url = isset($result['mp4url']) ? $result['mp4url'] : '';
                }
            }
        }
        $url = isset($mp4Url) ? $mp4Url : $this->getPlayUrl($file, $context);
        return $this->render('player/show.html.twig', array(
            'file'             => $file,
            'url'              => isset($url) ? $url : null,
            'context'          => $context,
            'player'           => $player,
            'agentInWhiteList' => $agentInWhiteList
        ));
    }


    protected function getPlayUrl($file, $context)
    {

        if ($file['storage'] == 'cloud') {
            if (!empty($file['metas2']) && !empty($file['metas2']['sd']['key'])) {
                if (isset($file['convertParams']['convertor']) && ($file['convertParams']['convertor'] == 'HLSEncryptedVideo')) {
                    $hideBeginning            = isset($context['hideBeginning']) ? $context['hideBeginning'] : false;
                    $context['hideBeginning'] = $this->isHiddenVideoHeader($hideBeginning);
                    $token                    = $this->makeToken('hls.playlist', $file['id'], $context);
                    $params                   = array(
                        'id'    => $file['id'],
                        'token' => $token['token']
                    );
                    return $this->generateUrl('hls_playlist', $params, true);
                } else {
                    $factory = new CloudClientFactory();
                    $client  = $factory->createClient();
                    $result  = $client->generateHLSQualitiyListUrl($file['metas2'], 3600);
                }
            } else {
                if (!empty($file['metas']) && !empty($file['metas']['hd']['key'])) {
                    $key = $file['metas']['hd']['key'];
                } else {
                    $key = $file['hashId'];
                }

                if ($key) {
                    $result = $this->getMaterialLibService()->player($file['globalId']);
                }
            }
            return isset($result['url']) ? $result['url'] : '';
        } else {
            $token = $this->makeToken('local.media', $file['id']);
            return $this->generateUrl('player_local_media', array(
                'id'    => $file['id'],
                'token' => $token['token']
            ));
        }
    }

    protected function makeToken($type, $fileId, $context = array())
    {
        $fields = array(

            'data'     => array(
                'id' => $fileId
            ),
            'times'    => 3,
            'duration' => 3600,
            'userId'   => $this->getUser()->getId()
        );

        if (isset($context['watchTimeLimit'])) {
            $fields['data']['watchTimeLimit'] = $context['watchTimeLimit'];
        }

        if (isset($context['hideBeginning'])) {
            $fields['data']['hideBeginning'] = $context['hideBeginning'];
        }

        $token = $this->getTokenService()->makeToken($type, $fields);

        return $token;
    }

    protected function getPlayer($file)
    {
        switch ($file['storage']) {
            case 'local':
                return $file["type"] == 'audio' ? 'audio-player' : 'local-video-player';
            case 'cloud':
                return 'balloon-cloud-video-player';
            default:
                return null;
        }
    }

    protected function isHiddenVideoHeader($isHidden = false)
    {
        $storage = $this->setting("storage");
        if (!empty($storage) && array_key_exists("video_header", $storage) && $storage["video_header"] && !$isHidden) {
            return false;
        } else {
            return true;
        }
    }

    protected function agentInWhiteList($userAgent)
    {
        $whiteList = array("iPhone", "iPad", "Android", "HTC");

        return ArrayToolkit::some($whiteList, function ($agent) use ($userAgent) {
            return strpos($userAgent, $agent) > -1;
        });
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
    }

    /**
     * @return MaterialLibService
     */
    protected function getMaterialLibService()
    {
        return $this->getServiceKernel()->createService('MaterialLib:MaterialLib.MaterialLibService');
    }

    protected function getTokenService()
    {
        return ServiceKernel::instance()->createService('User:TokenService');
    }
}