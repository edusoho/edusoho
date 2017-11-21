<?php

namespace AppBundle\Controller;

use AppBundle\Common\FileToolkit;
use AppBundle\Common\ArrayToolkit;
use Biz\User\Service\TokenService;
use Biz\CloudPlatform\CloudAPIFactory;
use Biz\File\Service\UploadFileService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Biz\MaterialLib\Service\MaterialLibService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PlayerController extends BaseController
{
    public function showAction(Request $request, $id, $isPart = false, $context = array())
    {
        $ssl = $request->isSecure() ? true : false;

        $file = $this->getUploadFileService()->getFullFile($id);
        if (empty($file)) {
            throw $this->createNotFoundException('file not found');
        }
        if (!in_array($file['type'], array('audio', 'video'))) {
            throw $this->createAccessDeniedException("player does not support  file type: {$file['type']}");
        }

        $player = $this->getPlayer($file);

        $agentInWhiteList = $this->agentInWhiteList($request->headers->get('user-agent'));

        $isEncryptionPlus = false;
        if ($file['type'] == 'video' && $file['storage'] == 'cloud') {
            $storageSetting = $this->getSettingService()->get('storage');

            $isEncryptionPlus = isset($storageSetting['enable_hls_encryption_plus']) && (bool) $storageSetting['enable_hls_encryption_plus'];

            if (!$this->isHiddenVideoHeader()) {
                // 加入片头信息
                $videoHeaderFile = $this->getUploadFileService()->getFileByTargetType('headLeader');
                if (!empty($videoHeaderFile) && $videoHeaderFile['convertStatus'] == 'success') {
                    $context['videoHeaderLength'] = $videoHeaderFile['length'];
                }
            }

            if (!empty($file['convertParams']['hasVideoWatermark'])) {
                $file['videoWatermarkEmbedded'] = 1;
            }

            $result = $this->getMaterialLibService()->player($file['globalId'], $ssl);

            if (isset($result['subtitles'])) {
                $this->filterSubtitles($result['subtitles']);
                $context['subtitles'] = $result['subtitles'];
            }

            // 临时修复手机浏览器端视频不能播放的问题
            if ($agentInWhiteList) {
                //手机浏览器不弹题
                $context['hideQuestion'] = 1;
                if (isset($file['mcStatus']) && $file['mcStatus'] == 'yes') {
                    $player = 'local-video-player';
                    $mp4Url = isset($result['mp4url']) ? $result['mp4url'] : '';
                    $isEncryptionPlus = false;
                }
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
        );

        if ($isPart) {
            return $this->render('player/play.html.twig', $params);
        }

        return $this->render('player/show.html.twig', $params);
    }

    public function localMediaAction(Request $request, $id, $token)
    {
        $file = $this->getUploadFileService()->getFile($id);

        if (empty($file)) {
            throw $this->createNotFoundException();
        }

        if (!in_array($file['type'], array('audio', 'video'))) {
            throw $this->createAccessDeniedException();
        }

        $token = $this->getTokenService()->verifyToken('local.media', $token);
        if (!$token || $token['userId'] != $this->getCurrentUser()->getId()) {
            throw $this->createAccessDeniedException();
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
            throw $this->createNotFoundException();
        }

        $dataId = is_array($token['data']) ? $token['data']['globalId'] : $token['data'];

        if ($dataId != ($globalId.$level)) {
            throw $this->createNotFoundException();
        }

        $file = $this->getMaterialLibService()->get($globalId);

        if (empty($file)) {
            throw $this->createNotFoundException();
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
            throw $this->createNotFoundException();
        }

        $dataId = is_array($token['data']) ? $token['data']['globalId'] : $token['data'];

        if ($dataId != $globalId) {
            throw $this->createNotFoundException();
        }

        $file = $this->getMaterialLibService()->get($globalId);

        if (empty($file)) {
            throw $this->createNotFoundException();
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

    protected function getPlayUrl($file, $context, $ssl)
    {
        if ($file['storage'] == 'cloud') {
            if (!empty($file['metas2']) && !empty($file['metas2']['sd']['key'])) {
                if (isset($file['convertParams']['convertor']) && ($file['convertParams']['convertor'] == 'HLSEncryptedVideo')) {
                    $hideBeginning = isset($context['hideBeginning']) ? $context['hideBeginning'] : false;
                    $context['hideBeginning'] = $this->isHiddenVideoHeader($hideBeginning);
                    $token = $this->makeToken('hls.playlist', $file['id'], $context);
                    $params = array(
                        'id' => $file['id'],
                        'token' => $token['token'],
                    );

                    return $this->generateUrl('hls_playlist', $params, true);
                } else {
                    throw new \RuntimeException('当前视频格式不能被播放！');
                }
            } else {
                if (!empty($file['metas']) && !empty($file['metas']['hd']['key'])) {
                    $key = $file['metas']['hd']['key'];
                } else {
                    $key = $file['hashId'];
                }

                if ($key) {
                    $result = $this->getMaterialLibService()->player($file['globalId'], $ssl);
                }
            }

            return isset($result['url']) ? $result['url'] : '';
        } else {
            $token = $this->makeToken('local.media', $file['id']);

            return $this->generateUrl('player_local_media', array(
                'id' => $file['id'],
                'token' => $token['token'],
            ));
        }
    }

    protected function makeToken($type, $fileId, $context = array())
    {
        $fields = array(
            'data' => array(
                'id' => $fileId,
            ),
            'times' => 10,
            'duration' => 3600,
            'userId' => $this->getUser()->getId(),
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
        switch ($file['type']) {
            case 'audio':
                return 'audio-player';
            case 'video':
                return $file['storage'] == 'local' ? 'local-video-player' : 'balloon-cloud-video-player';
            default:
                return null;
        }
    }

    protected function isHiddenVideoHeader($isHidden = false)
    {
        $storage = $this->setting('storage');
        if (!empty($storage) && array_key_exists('video_header', $storage) && $storage['video_header'] && !$isHidden) {
            return false;
        } else {
            return true;
        }
    }

    protected function agentInWhiteList($userAgent)
    {
        $whiteList = array('iPhone', 'iPad', 'Android', 'HTC');

        return ArrayToolkit::some($whiteList, function ($agent) use ($userAgent) {
            return strpos($userAgent, $agent) > -1;
        });
    }

    private function filterSubtitles(&$subtitles)
    {
        foreach ($subtitles as &$subtitle) {
            $subtitle['name'] = rtrim($subtitle['name'], '.srt');
        }
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
}
