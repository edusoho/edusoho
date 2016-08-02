<?php

namespace MaterialLib\MaterialLibBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\WebBundle\Controller\BaseController;
use Topxia\Service\CloudPlatform\CloudAPIFactory;

class GlobalFilePlayerController extends BaseController
{
    public function playerAction(Request $request, $globalId)
    {
        $file = $this->getCloudFileService()->getByGlobalId($globalId);

        if (empty($file)) {
            throw $this->createNotFoundException();
        }

        if ($file['type'] == 'video') {
            return $this->videoPlayer($file, $request);
        } elseif ($file['type'] == 'ppt') {
            return $this->render('MaterialLibBundle:Player:ppt-player.html.twig', array(
                'file' => $file
            ));
        } elseif ($file["type"] == 'audio') {
            return $this->audioPlayer($file);
        } elseif ($file["type"] == 'document') {
            return $this->render('MaterialLibBundle:Player:document-player.html.twig', array(
                'file' => $file
            ));
        } elseif ($file["type"] == 'image') {
            $file = $this->getMaterialLibService()->download($file['id']);
            return $this->render('MaterialLibBundle:Player:image-player.html.twig', array(
                'file' => $file
            ));
        } elseif ($file["type"] == 'flash') {
            $file = $this->getMaterialLibService()->player($globalId);
            return $this->render('MaterialLibBundle:Player:flash-player.html.twig', array(
                'file' => $file
            ));
        }
    }

    public function pptAction(Request $request, $globalId)
    {
        $file = $this->getMaterialLibService()->player($globalId);

        if (empty($file)) {
            throw $this->createNotFoundException();
        }

        return $this->createJsonResponse($file);
    }

    public function documentAction(Request $request, $globalId)
    {
        $file = $this->getMaterialLibService()->player($globalId);

        if (empty($file)) {
            throw $this->createNotFoundException();
        }

        return $this->createJsonResponse($file);
    }

    public function audioPlayer($file)
    {
        $result = $this->getMaterialLibService()->player($file['no']);
        return $this->render('MaterialLibBundle:Player:global-video-player.html.twig', array(
            'file'             => $file,
            'url'              => $result['url'],
            'player'           => 'audio-player',
            'agentInWhiteList' => $this->agentInWhiteList($this->getRequest()->headers->get("user-agent"))
        ));
    }

    protected function videoPlayer($file, $request)
    {
        $url = $this->getPlayUrl($file);

        return $this->render('MaterialLibBundle:Player:global-video-player.html.twig', array(
            'file'             => $file,
            'url'              => $url,
            'player'           => 'balloon-cloud-video-player',
            'params'           => $request->query->all(),
            'agentInWhiteList' => $this->agentInWhiteList($this->getRequest()->headers->get("user-agent"))
        ));
    }

    protected function getPlayUrl($file)
    {
        if (!in_array($file["type"], array("audio", "video"))) {
            throw $this->createAccessDeniedException();
        }

        $token = $this->makeToken('hls.playlist', $file['no']);

        $params = array(
            'globalId' => $file['no'],
            'token'    => $token['token']
        );

        return $this->generateUrl('global_file_hls_playlist', $params, true);
    }

    public function playlistAction(Request $request, $globalId, $token)
    {
        $token = $this->getTokenService()->verifyToken('hls.playlist', $token);

        if (empty($token)) {
            throw $this->createNotFoundException();
        }

        $dataId = is_array($token['data']) ? $token['data']['globalId'] : $token['data'];

        if ($dataId != $globalId) {
            throw $this->createNotFoundException();
        }

        $file = $this->getCloudFileService()->getByGlobalId($globalId);

        if (empty($file)) {
            throw $this->createNotFoundException();
        }

        $streams = array();

        foreach (array('sd', 'hd', 'shd') as $level) {
            if (empty($file['metas']['levels'][$level])) {
                continue;
            }

            $tokenFields = array(
                'data'     => array(
                    'globalId' => $file['no'].$level
                ),
                'times'    => $this->agentInWhiteList($request->headers->get("user-agent")) ? 0 : 1,
                'duration' => 3600
            );

            if (!empty($token['userId'])) {
                $tokenFields['userId'] = $token['userId'];
            }

            $token = $this->getTokenService()->makeToken('hls.stream', $tokenFields);

            $params = array(
                'globalId' => $file['no'],
                'level'    => $level,
                'token'    => $token['token']
            );

            $streams[$level] = $this->generateUrl('global_file_hls_stream', $params, true);
        }

        $api = CloudAPIFactory::create('leaf');

        $qualities = array(
            'video' => $file['directives']['videoQuality'],
            'audio' => $file['directives']['audioQuality']
        );

        $playlist = $api->get('/hls/playlist', array(
            'streams'   => $streams,
            'qualities' => $qualities
        ));

        if (empty($playlist['playlist'])) {
            return $this->createMessageResponse('error', '生成视频播放列表失败！');
        }

        return new Response($playlist['playlist'], 200, array(
            'Content-Type'        => 'application/vnd.apple.mpegurl',
            'Content-Disposition' => 'inline; filename="playlist.m3u8"'
        ));
    }

    public function streamAction(Request $request, $globalId, $level, $token)
    {
        $token = $this->getTokenService()->verifyToken('hls.stream', $token);

        if (empty($token)) {
            throw $this->createNotFoundException();
        }

        $dataId = is_array($token['data']) ? $token['data']['globalId'] : $token['data'];

        if ($dataId != ($globalId.$level)) {
            throw $this->createNotFoundException();
        }

        $file = $this->getCloudFileService()->getByGlobalId($globalId);

        if (empty($file)) {
            throw $this->createNotFoundException();
        }

        if (empty($file['metas']['levels'][$level]['key'])) {
            throw $this->createNotFoundException();
        }

        $tokenFields = array(
            'data'     => array(
                'globalId'      => $file['no'],
                'level'         => $level,
                'keyencryption' => 0
            ),
            'times'    => 1,
            'duration' => 3600
        );

        if (!empty($token['userId'])) {
            $tokenFields['userId'] = $token['userId'];
        }

        $token = $this->getTokenService()->makeToken('hls.clef', $tokenFields);

        $params           = array();
        $params['keyUrl'] = $this->generateUrl('global_file_hls_clef', array(
            'globalId' => $file['no'],
            'token'    => $token['token']
        ), true);
        $params['key']    = $file['metas']['levels'][$level]['key'];
        $params['fileId'] = $file['extno'];

        $api = CloudAPIFactory::create('leaf');

        $stream = $api->get('/hls/stream', $params);

        if (empty($stream['stream'])) {
            return $this->createMessageResponse('error', '生成视频播放地址失败！');
        }

        return new Response($stream['stream'], 200, array(
            'Content-Type'        => 'application/vnd.apple.mpegurl',
            'Content-Disposition' => 'inline; filename="stream.m3u8"'
        ));
    }

    public function clefAction(Request $request, $globalId, $token)
    {
        $token = $this->getTokenService()->verifyToken('hls.clef', $token);

        if (empty($token)) {
            return $this->makeFakeTokenString();
        }

        $dataId = is_array($token['data']) ? $token['data']['globalId'] : $token['data'];

        if ($dataId != $globalId) {
            return $this->makeFakeTokenString();
        }

        $file = $this->getCloudFileService()->getByGlobalId($globalId);

        if (empty($file)) {
            return $this->makeFakeTokenString();
        }

        if (empty($file['metas']['levels'][$token['data']['level']]['hlsKey'])) {
            return $this->makeFakeTokenString();
        }

        return new Response($file['metas']['levels'][$token['data']['level']]['hlsKey']);
    }

    protected function makeToken($type, $globalId)
    {
        $fileds = array(
            'data'     => array(
                'globalId' => $globalId
            ),
            'times'    => 3,
            'duration' => 3600,
            'userId'   => $this->getCurrentUser()->getId()
        );

        $token = $this->getTokenService()->makeToken($type, $fileds);
        return $token;
    }

    protected function getCloudFileService()
    {
        return $this->getServiceKernel()->createService('CloudFile.CloudFileService');
    }

    protected function getTokenService()
    {
        return $this->getServiceKernel()->createService('User.TokenService');
    }

    protected function getMaterialLibService()
    {
        return $this->getServiceKernel()->createService('MaterialLib:MaterialLib.MaterialLibService');
    }
}
