<?php

namespace AppBundle\Controller;

use AppBundle\Common\FileToolkit;
use Biz\CloudPlatform\CloudAPIFactory;
use Biz\File\Service\UploadFileService;
use Biz\File\UploadFileException;
use Biz\MaterialLib\Service\MaterialLibService;
use Biz\Player\PlayerException;
use Biz\Player\Service\PlayerService;
use Biz\S2B2C\Service\FileSourceService;
use Biz\S2B2C\Service\S2B2CFacadeService;
use Biz\User\Service\TokenService;
use Biz\User\TokenException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PlayerController extends BaseController
{
    public function showAction(Request $request, $id, $isPart = false, $context = [], $rememberLastPos = true)
    {
        $file = $this->getUploadFileService()->getFullFile($id);
        if (empty($file)) {
            $this->createNewException(UploadFileException::NOTFOUND_FILE());
        }
        if (!in_array($file['type'], ['audio', 'video'])) {
            $this->createNewException(PlayerException::NOT_SUPPORT_TYPE());
        }

        // 获取播放必须的token和resNo，以及一些个性化播放器参数
        $playerContext = $this->getResourceFacadeService()->getPlayerContext($file);
        if (is_array($context)) {
            $playerContext = array_merge($playerContext, $context);
        }

        $params = [
            'file' => $file,
            'cloudSdk' => $file['type'],
            'context' => $playerContext,
            'rememberLastPos' => $rememberLastPos,
        ];

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

    public function localMediaAction(Request $request, $id, $token, $ext)
    {
        $file = $this->getUploadFileService()->getFile($id);

        if (empty($file)) {
            $this->createNewException(UploadFileException::NOTFOUND_FILE());
        }

        if (!in_array($file['type'], ['audio', 'video'])) {
            $this->createNewException(PlayerException::NOT_SUPPORT_TYPE());
        }

        $token = $this->getTokenService()->verifyToken('local.media', $token);
        if (!$token) {
            $this->createNewException(TokenException::TOKEN_INVALID());
        }

        $response = BinaryFileResponse::create($file['fullpath'], 200, [], false);
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

        $tokenFields = [
            'data' => [
                'globalId' => $file['no'],
                'level' => $level,
                'keyencryption' => 0,
            ],
            'times' => 1,
            'duration' => 3600,
        ];

        if (!empty($token['userId'])) {
            $tokenFields['userId'] = $token['userId'];
        }

        $token = $this->getTokenService()->makeToken('hls.clef', $tokenFields);

        $params = [];
        $params['keyUrl'] = $this->generateUrl('global_file_hls_clef', [
            'globalId' => $file['no'],
            'token' => $token['token'],
        ], UrlGeneratorInterface::ABSOLUTE_URL);
        $params['key'] = $file['metas']['levels'][$level]['key'];
        $params['fileId'] = $file['id'];

        $api = CloudAPIFactory::create('leaf');

        $stream = $api->get('/hls/stream', $params);

        if (empty($stream['stream'])) {
            return $this->createMessageResponse('error', '生成视频播放地址失败！');
        }

        return new Response($stream['stream'], 200, [
            'Content-Type' => 'application/vnd.apple.mpegurl',
            'Content-Disposition' => 'inline; filename="stream.m3u8"',
        ]);
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

        $streams = [];

        foreach (['sd', 'hd', 'shd'] as $level) {
            if (empty($file['metas']['levels'][$level])) {
                continue;
            }

            $tokenFields = [
                'data' => [
                    'globalId' => $file['no'].$level,
                ],
                'times' => $this->agentInWhiteList($request->headers->get('user-agent')) ? 0 : 1,
                'duration' => 3600,
            ];

            if (!empty($token['userId'])) {
                $tokenFields['userId'] = $token['userId'];
            }

            $token = $this->getTokenService()->makeToken('hls.stream', $tokenFields);

            $params = [
                'globalId' => $file['no'],
                'level' => $level,
                'token' => $token['token'],
            ];

            $streams[$level] = $this->generateUrl('global_file_hls_stream', $params, UrlGeneratorInterface::ABSOLUTE_URL);
        }

        $api = CloudAPIFactory::create('leaf');

        $qualities = [
            'video' => $file['directives']['videoQuality'],
            'audio' => $file['directives']['audioQuality'],
        ];

        $uri = '/hls/playlist/json';
        $params = ['streams' => $streams, 'qualities' => $qualities];
        if ('supplier' == $file['storage']) {
            $fileInfo = $this->getS2B2CFileSourceService()->getFullFileInfo($file);
            $playlist = $this->getS2B2CFacedService()->getS2B2CService()->getProductHlsPlaylistJson($uri, $fileInfo, $params);
        } else {
            $playlist = $api->get($uri, $params);
        }

        return $this->createJsonResponse($playlist);
    }

    /**
     * @return FileSourceService
     */
    protected function getS2B2CFileSourceService()
    {
        return $this->createService('S2B2C:FileSourceService');
    }

    /**
     * @return S2B2CFacadeService
     */
    protected function getS2B2CFacedService()
    {
        return $this->createService('S2B2C:S2B2CFacadeService');
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

    protected function getResourceFacadeService()
    {
        return $this->getBiz()->service('CloudPlatform:ResourceFacadeService');
    }
}
