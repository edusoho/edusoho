<?php

namespace AppBundle\Controller;

use AppBundle\Common\FileToolkit;
use Biz\File\UploadFileException;
use Biz\Player\PlayerException;
use Biz\Player\Service\PlayerService;
use Biz\User\Service\TokenService;
use Biz\CloudPlatform\CloudAPIFactory;
use Biz\File\Service\UploadFileService;
use Biz\User\TokenException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Biz\MaterialLib\Service\MaterialLibService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PlayerController extends BaseController
{
    public function showAction(Request $request, $id, $isPart = false, $context = array(), $remeberLastPos = true)
    {
        $ssl = $request->isSecure() ? true : false;

        $file = $this->getUploadFileService()->getFullFile($id);
        if (empty($file)) {
            $this->createNewException(UploadFileException::NOTFOUND_FILE());
        }
        if (!in_array($file['type'], array('audio', 'video'))) {
            $this->createNewException(PlayerException::NOT_SUPPORT_TYPE());
        }

        $player = $this->getPlayerService()->getAudioAndVideoPlayerType($file);

        $agentInWhiteList = $this->getPlayerService()->agentInWhiteList($request->headers->get('user-agent'));

        $isEncryptionPlus = false;

        //音频无论是否开启教育云都会去使用cloudsdk
        if ('audio' == $file['type']) {
            $cloudSdk = 'audio'; //webExtension->getCloudSdkUrl
        }
        if ('video' == $file['type'] && 'cloud' == $file['storage']) {
            $cloudSdk = 'video'; //webExtension->getCloudSdkUrl
            $videoPlayer = $this->getPlayerService()->getVideoFilePlayer($file, $agentInWhiteList, $context, $ssl);
            $isEncryptionPlus = $videoPlayer['isEncryptionPlus'];
            $context = $videoPlayer['context'];
            if (!empty($videoPlayer['mp4Url'])) {
                $mp4Url = $videoPlayer['mp4Url'];
            }
        }
        $url = isset($mp4Url) ? $mp4Url : $this->getPlayUrl($file, $context, $ssl);

        $params = array(
            'file' => $file,
            'url' => isset($url) ? $url : null,
            'context' => $context,
            'player' => $player,
            'agentInWhiteList' => $agentInWhiteList,
            'isEncryptionPlus' => $isEncryptionPlus,
            'cloudSdk' => isset($cloudSdk) ? $cloudSdk : null,
            'remeberLastPos' => $remeberLastPos,
        );

        if ($isPart) {
            return $this->render('player/play.html.twig', $params);
        }

        return $this->render('player/show.html.twig', $params);
    }

    protected function getPlayUrl($file, $context, $ssl)
    {
        $result = $this->getPlayerService()->getVideoPlayUrl($file, $context, $ssl);
        if (isset($result['url'])) {
            return $result['url'];
        }

        return $this->generateUrl($result['route'], $result['params'], $result['referenceType']);
    }

    public function localMediaAction(Request $request, $id, $token)
    {
        $file = $this->getUploadFileService()->getFile($id);

        if (empty($file)) {
            $this->createNewException(UploadFileException::NOTFOUND_FILE());
        }

        if (!in_array($file['type'], array('audio', 'video'))) {
            $this->createNewException(PlayerException::NOT_SUPPORT_TYPE());
        }

        $token = $this->getTokenService()->verifyToken('local.media', $token);
        if (!$token || $token['userId'] != $this->getCurrentUser()->getId()) {
            $this->createNewException(TokenException::TOKEN_INVALID());
        }

        $response = BinaryFileResponse::create($file['fullpath'], 200, array(), false);
        $response::trustXSendfileTypeHeader();

        $mimeType = FileToolkit::getMimeTypeByExtension($file['ext']);

        if ($mimeType) {
            $response->headers->set('Content-Type', $mimeType);
        }

        return $response;
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

        $file = $this->getMaterialLibService()->get($globalId);

        if (empty($file)) {
            $this->createNewException(UploadFileException::NOTFOUND_FILE());
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
        $params['fileId'] = $file['id'];

        $api = CloudAPIFactory::create('leaf');

        $stream = $api->get('/hls/stream', $params);

        if (empty($stream['stream'])) {
            return $this->createMessageResponse('error', '生成视频播放地址失败！');
        }

        return new Response($stream['stream'], 200, array(
            'Content-Type' => 'application/vnd.apple.mpegurl',
            'Content-Disposition' => 'inline; filename="stream.m3u8"',
        ));
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

        $file = $this->getMaterialLibService()->get($globalId);

        if (empty($file)) {
            $this->createNewException(UploadFileException::NOTFOUND_FILE());
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

        $playlist = $api->get('/hls/playlist/json', array('streams' => $streams, 'qualities' => $qualities));

        return $this->createJsonResponse($playlist);
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->getBiz()->service('File:UploadFileService');
    }

    /**
     * @return MaterialLibService
     */
    protected function getMaterialLibService()
    {
        return $this->getBiz()->service('MaterialLib:MaterialLibService');
    }

    /**
     * @return \Biz\System\Service\SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }

    /**
     * @return TokenService
     */
    protected function getTokenService()
    {
        return $this->getBiz()->service('User:TokenService');
    }

    /**
     * @return PlayerService
     */
    protected function getPlayerService()
    {
        return $this->getBiz()->service('Player:PlayerService');
    }
}
