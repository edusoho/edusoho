<?php

namespace AppBundle\Controller\MaterialLib;

use Biz\CloudFile\CloudFileException;
use Biz\CloudFile\Service\CloudFileService;
use Biz\CloudPlatform\CloudAPIFactory;
use AppBundle\Controller\BaseController;
use Biz\MaterialLib\Service\MaterialLibService;
use Biz\Player\PlayerException;
use Biz\User\TokenException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GlobalFilePlayerController extends BaseController
{
    public function playerAction(Request $request, $globalId)
    {
        $file = $this->getCloudFileService()->getByGlobalId($globalId);

        if (empty($file)) {
            $this->createNewException(CloudFileException::NOTFOUND_CLOUD_FILE());
        }
        if (in_array($file['type'], array('video', 'ppt', 'document'))) {
            return $this->globalPlayer($file, $request);
        } elseif ($file['type'] == 'audio') {
            return $this->audioPlayer($file, $request);
        } elseif (in_array($file['type'], array('image', 'flash'))) {
            return $this->commonPlayer($file, $request);
        }

        $this->createNewException(PlayerException::NOT_SUPPORT_TYPE());
    }

    public function globalDocumentPlayerAction(Request $request, $globalId)
    {
        $token = $request->query->get('token');

        return $this->render(
            'material-lib/player/global-document-player.html.twig',
            array(
                'globalId' => $globalId,
                'token' => $token,
            )
        );
    }

    public function globalPlayer($file, $request)
    {
        $ssl = $request->isSecure() ? true : false;
        $player = $this->getMaterialLibService()->player($file['globalId'], $ssl);

        return $this->render('material-lib/player/global-player.html.twig', array(
            'file' => $file,
            'player' => $player,
        ));
    }

    public function commonPlayer($file, $request)
    {
        $ssl = $request->isSecure() ? true : false;
        $player = $this->getMaterialLibService()->player($file['globalId'], $ssl);

        if (empty($player)) {
            $this->createNewException(CloudFileException::NOTFOUND_PLAYER());
        }

        return $this->render("material-lib/player/{$file['type']}-player.html.twig", array(
            'player' => $player,
        ));
    }

    public function audioPlayer($file, Request $request)
    {
        $ssl = $request->isSecure() ? true : false;
        $result = $this->getMaterialLibService()->player($file['no'], $ssl);

        return $this->render('material-lib/player/global-video-player.html.twig', array(
            'file' => $file,
            'url' => $result['url'],
            'player' => 'audio-player',
            'agentInWhiteList' => $this->agentInWhiteList($request->headers->get('user-agent')),
        ));
    }

    protected function videoPlayer($file, Request $request)
    {
        $url = $this->getPlayUrl($file);

        return $this->render('material-lib/player/global-video-player.html.twig', array(
            'file' => $file,
            'url' => $url,
            'player' => 'balloon-cloud-video-player',
            'params' => $request->query->all(),
            'agentInWhiteList' => $this->agentInWhiteList($request->headers->get('user-agent')),
        ));
    }

    protected function getPlayUrl($file)
    {
        if (!in_array($file['type'], array('audio', 'video'))) {
            $this->createNewException(PlayerException::NOT_SUPPORT_TYPE());
        }

        $token = $this->makeToken('hls.playlist', $file['no']);

        $params = array(
            'globalId' => $file['no'],
            'token' => $token['token'],
        );

        return $this->generateUrl('global_file_hls_playlist', $params, true);
    }

    public function playlistAction(Request $request, $globalId, $token)
    {
        $token = $this->getTokenService()->verifyToken('hls.playlist', $token);

        if (empty($token)) {
            $this->createNewException(TokenException::TOKEN_INVALID());
        }

        $dataId = is_array($token['data']) ? $token['data']['globalId'] : $token['data'];

        if ($dataId != $globalId) {
            throw $this->createNotFoundException();
        }

        $file = $this->getCloudFileService()->getByGlobalId($globalId);

        if (empty($file)) {
            $this->createNewException(CloudFileException::NOTFOUND_CLOUD_FILE());
        }

        $streams = array();

        foreach (array('sd', 'hd', 'shd') as $level) {
            if (empty($file['metas']['levels'][$level])) {
                continue;
            }

            $tokenFields = array(
                'data' => array(
                    'globalId' => $file['no'].$level,
                ),
                'times' => $this->agentInWhiteList($request->headers->get('user-agent')) ? 0 : 1,
                'duration' => 3600,
            );

            if (!empty($token['userId'])) {
                $tokenFields['userId'] = $token['userId'];
            }

            $token = $this->getTokenService()->makeToken('hls.stream', $tokenFields);

            $params = array(
                'globalId' => $file['no'],
                'level' => $level,
                'token' => $token['token'],
            );

            $streams[$level] = $this->generateUrl('global_file_hls_stream', $params, true);
        }

        $api = CloudAPIFactory::create('leaf');

        $qualities = array(
            'video' => $file['directives']['videoQuality'],
            'audio' => $file['directives']['audioQuality'],
        );

        $playlist = $api->get('/hls/playlist', array(
            'streams' => $streams,
            'qualities' => $qualities,
        ));

        if (empty($playlist['playlist'])) {
            return $this->createMessageResponse('error', '生成视频播放列表失败！');
        }

        return new Response($playlist['playlist'], 200, array(
            'Content-Type' => 'application/vnd.apple.mpegurl',
            'Content-Disposition' => 'inline; filename="playlist.m3u8"',
        ));
    }

    public function streamAction(Request $request, $globalId, $level, $token)
    {
        $token = $this->getTokenService()->verifyToken('hls.stream', $token);

        if (empty($token)) {
            $this->createNewException(TokenException::TOKEN_INVALID());
        }

        $dataId = is_array($token['data']) ? $token['data']['globalId'] : $token['data'];

        if ($dataId != ($globalId.$level)) {
            throw $this->createNotFoundException();
        }

        $file = $this->getCloudFileService()->getByGlobalId($globalId);

        if (empty($file)) {
            $this->createNewException(CloudFileException::NOTFOUND_CLOUD_FILE());
        }

        if (empty($file['metas']['levels'][$level]['key'])) {
            throw $this->createNotFoundException();
        }

        $tokenFields = array(
            'data' => array(
                'globalId' => $file['no'],
                'level' => $level,
                'keyencryption' => 0,
            ),
            'times' => 1,
            'duration' => 3600,
        );

        if (!empty($token['userId'])) {
            $tokenFields['userId'] = $token['userId'];
        }

        $token = $this->getTokenService()->makeToken('hls.clef', $tokenFields);

        $params = array();
        $params['keyUrl'] = $this->generateUrl('global_file_hls_clef', array(
            'globalId' => $file['no'],
            'token' => $token['token'],
        ), true);
        $params['key'] = $file['metas']['levels'][$level]['key'];
        $params['fileId'] = $file['extno'];

        $api = CloudAPIFactory::create('leaf');

        $stream = $api->get('/hls/stream', $params);

        if (empty($stream['stream'])) {
            return $this->createMessageResponse('error', $this->getServiceKernel()->trans('生成视频播放地址失败！'));
        }

        return new Response($stream['stream'], 200, array(
            'Content-Type' => 'application/vnd.apple.mpegurl',
            'Content-Disposition' => 'inline; filename="stream.m3u8"',
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
            'data' => array(
                'globalId' => $globalId,
            ),
            'times' => 3,
            'duration' => 3600,
            'userId' => $this->getCurrentUser()->getId(),
        );

        $token = $this->getTokenService()->makeToken($type, $fileds);

        return $token;
    }

    /**
     * @return CloudFileService
     */
    protected function getCloudFileService()
    {
        return $this->createService('CloudFile:CloudFileService');
    }

    protected function getTokenService()
    {
        return $this->createService('User:TokenService');
    }

    /**
     * @return MaterialLibService
     */
    protected function getMaterialLibService()
    {
        return $this->createService('MaterialLib:MaterialLibService');
    }
}
