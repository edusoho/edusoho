<?php

namespace AppBundle\Controller;

use Biz\File\UploadFileException;
use Biz\User\Service\UserService;
use Biz\User\Service\TokenService;
use Biz\CloudPlatform\CloudAPIFactory;
use Biz\System\Service\SettingService;
use Biz\File\Service\UploadFileService;
use Biz\User\TokenException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class HLSBaseController extends BaseController
{
    public function playlistAction(Request $request, $id, $token)
    {
        $line = $request->query->get('line', null);
        $format = $request->query->get('format', '');
        $levelParam = $request->query->get('level', '');

        $token = $this->getTokenService()->verifyToken('hls.playlist', $token);
        $fromApi = isset($token['data']['fromApi']) ? $token['data']['fromApi'] : false;
        $clientIp = $request->getClientIp();

        if (empty($token)) {
            $this->createNewException(TokenException::TOKEN_INVALID());
        }

        $dataId = is_array($token['data']) ? $token['data']['id'] : $token['data'];

        if ($dataId != $id) {
            throw $this->createNotFoundException();
        }

        $file = $this->getFile($id, $token);

        if (empty($file)) {
            $this->createNewException(UploadFileException::NOTFOUND_FILE());
        }

        $streams = array();
        $inWhiteList = $this->agentInWhiteList($request->headers->get('user-agent'));

        $metas = $file[$this->getMediaAttr()];

        foreach (array('sd', 'hd', 'shd') as $level) {
            if (empty($metas[$level])) {
                continue;
            }

            if (empty($levelParam) || (!empty($levelParam) && strtolower($levelParam) == $level)) {
                $tokenFields = array(
                    'data' => array(
                        'id' => $file['id'].$level,
                        'fromApi' => $fromApi,
                    ),
                    'times' => $inWhiteList ? 0 : 1,
                    'duration' => 3600,
                );

                if (!empty($token['data']['replayId'])) {
                    $tokenFields['data']['replayId'] = $token['data']['replayId'];
                    $tokenFields['data']['type'] = $token['data']['type'];
                }

                if (!empty($token['userId'])) {
                    $tokenFields['userId'] = $token['userId'];
                }

                if (isset($token['data']['watchTimeLimit'])) {
                    $tokenFields['data']['watchTimeLimit'] = $token['data']['watchTimeLimit'];
                }

                if (isset($token['data']['hideBeginning'])) {
                    $tokenFields['data']['hideBeginning'] = $token['data']['hideBeginning'];
                }

                $token = $this->getTokenService()->makeToken('hls.stream', $tokenFields);
            } else {
                $token['token'] = $this->getTokenService()->makeFakeTokenString();
            }

            $params = array(
                'id' => $file['id'],
                'level' => $level,
                'token' => $token['token'],
            );

            if ($line) {
                $params['line'] = $line;
            }

            if ($request->isSecure()) {
                $params['protocol'] = 'https';
            }

            $streams[$level] = $this->generateUrl("hls_{$this->getRoutingPrefix()}stream", $params, true);
        }

        $qualities = array(
            'video' => $file['convertParams']['videoQuality'],
            'audio' => $file['convertParams']['audioQuality'],
        );
        $api = CloudAPIFactory::create('leaf');

        //新版api需要返回json形式的m3u8
        if ('json' == strtolower($format)) {
            $playlist = $api->get('/hls/playlist/json', array('streams' => $streams, 'qualities' => $qualities));

            return $this->createJsonResponse($playlist);
        }

        $playlist = $api->get('/hls/playlist', array(
            'streams' => $streams,
            'qualities' => $qualities,
            'clientIp' => $clientIp,
        ));

        if (empty($playlist['playlist'])) {
            return $this->createMessageResponse('error', '生成视频播放列表失败！');
        }

        return $this->responseEnhanced($playlist['playlist'], array(
            'Content-Type' => 'application/vnd.apple.mpegurl',
            'Content-Disposition' => 'inline; filename="playlist.m3u8"',
        ));
    }

    public function streamAction(Request $request, $id, $level, $token)
    {
        $token = $this->getTokenService()->verifyToken('hls.stream', $token);
        $fromApi = isset($token['data']['fromApi']) ? $token['data']['fromApi'] : false;
        $clientIp = $request->getClientIp();
        if (empty($token)) {
            $this->createNewException(TokenException::TOKEN_INVALID());
        }

        $streamToken = $token;

        $dataId = is_array($token['data']) ? $token['data']['id'] : $token['data'];

        if ($dataId != ($id.$level)) {
            throw $this->createNotFoundException();
        }

        $file = $this->getFile($id, $token);

        if (empty($file)) {
            $this->createNewException(UploadFileException::NOTFOUND_FILE());
        }

        if (empty($file[$this->getMediaAttr()][$level]['key'])) {
            throw $this->createNotFoundException();
        }

        $params = array();
        $params['accessKey'] = $this->setting('storage.cloud_access_key');
        $params['key'] = $file[$this->getMediaAttr()][$level]['key'];
        $params['fileId'] = $file['id'];
        $params['fileGlobalId'] = $file['globalId'];
        $params['clientIp'] = $clientIp;

        if (!empty($token['data']['watchTimeLimit'])) {
            $params['limitSecond'] = $token['data']['watchTimeLimit'];
        }

        $inWhiteList = $this->agentInWhiteList($request->headers->get('user-agent'));
        $tokenFields = array(
            'data' => array(
                'id' => $file['id'],
                'level' => $level,
                'fromApi' => $fromApi,
            ),
            'times' => $inWhiteList ? 0 : 1,
            'duration' => 3600,
        );

        if (!empty($token['data']['replayId'])) {
            $tokenFields['data']['replayId'] = $token['data']['replayId'];
            $tokenFields['data']['type'] = $token['data']['type'];
        }

        if (!empty($token['userId'])) {
            $tokenFields['userId'] = $token['userId'];
            $params['userId'] = $token['userId'];
            $user = $this->getUserService()->getUser($token['userId']);
            $params['userName'] = $user['nickname'];
        }

        $token = $this->getTokenService()->makeToken('hls.clef', $tokenFields);

        $params['keyUrl'] = $this->generateUrl("hls_{$this->getRoutingPrefix()}clef", array('id' => $file['id'], 'token' => $token['token']), true);

        $hideBeginning = isset($streamToken['data']['hideBeginning']) ? $streamToken['data']['hideBeginning'] : false;
        if (!$inWhiteList && !$this->getWebExtension()->isHiddenVideoHeader($hideBeginning)) {
            $beginning = $this->getVideoBeginning($request, $level, array(
                'userId' => $token['userId'],
                'fromApi' => $fromApi,
            ));

            if ($beginning['beginningKey']) {
                $params = array_merge($params, $beginning);
            }
        }

        $line = $request->query->get('line');

        if (!empty($line)) {
            $params['line'] = $line;
        }

        if ($request->isSecure()) {
            $params['protocol'] = 'https';
        }

        $api = CloudAPIFactory::create('leaf');

        $stream = $api->get('/hls/stream', $params);

        if (empty($stream['stream'])) {
            return $this->createMessageResponse('error', '生成视频播放地址失败！');
        }

        return $this->responseEnhanced(
            $stream['stream'],
            array(
                'Content-Type' => 'application/vnd.apple.mpegurl',
                'Content-Disposition' => 'inline; filename="stream.m3u8"',
            )
        );
    }

    public function clefAction(Request $request, $id, $token)
    {
        $isMobileUserAgent = $this->agentInWhiteList($request->headers->get('user-agent'));
        $token = $this->getTokenService()->verifyToken('hls.clef', $token);
        if (empty($token)) {
            return $this->makeFakeTokenString();
        }

        $dataId = is_array($token['data']) ? $token['data']['id'] : $token['data'];

        if ($dataId != $id) {
            return $this->makeFakeTokenString();
        }

        $file = $this->getFile($id, $token);

        if (empty($file)) {
            return $this->makeFakeTokenString();
        }

        if (empty($file[$this->getMediaAttr()][$token['data']['level']]['hlsKey'])) {
            return $this->makeFakeTokenString();
        }

        if (!empty($token['data']['fromApi'])) {
            return $this->responseEnhanced($file[$this->getMediaAttr()][$token['data']['level']]['hlsKey']);
        }

        if ($this->isHlsEncryptionPlusEnabled() || !$isMobileUserAgent) {
            $api = CloudAPIFactory::create('leaf');
            $result = $api->get("/hls/clef_plus/{$file[$this->getMediaAttr()][$token['data']['level']]['hlsKey']}");

            return $this->responseEnhanced($result['key']);
        }

        return $this->responseEnhanced($file[$this->getMediaAttr()][$token['data']['level']]['hlsKey']);
    }

    /**
     * 取被播放m3u8的属性
     */
    abstract protected function getMediaAttr();

    /**
     * 用于生成路由,
     *   hls_{$this->getRoutingPrefix()}clef
     *   hls_{$this->getRoutingPrefix()}stream
     */
    abstract protected function getRoutingPrefix();

    protected function responseEnhanced($responseContent, $headers = array())
    {
        $headers = array_merge(
            array(
                'Access-Control-Allow-Headers' => 'origin, content-type, accept',
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'POST, GET, PUT, DELETE, PATCH, OPTIONS',
            ),
            $headers
        );

        if (is_string($responseContent)) {
            $headers['Content-Length'] = strlen($responseContent);
        }

        return new Response($responseContent, 200, $headers);
    }

    protected function makeFakeTokenString()
    {
        $fakeKey = $this->getTokenService()->makeFakeTokenString();

        return $this->responseEnhanced($fakeKey);
    }

    protected function isHlsEncryptionPlusEnabled()
    {
        $enabled = $this->setting('storage.enable_hls_encryption_plus');

        return $enabled;
    }

    protected function getVideoBeginning(Request $request, $level, $params = array())
    {
        $beginning = array(
            'beginningKey' => null,
            'beginningKeyUrl' => null,
        );

        $storage = $this->getSettingService()->get('storage');

        if (!empty($storage['video_header'])) {
            $file = $this->getUploadFileService()->getFileByTargetType('headLeader');
            $beginnings = $file[$this->getMediaAttr()];
            $levels = array($level);
            $levels = array_merge($levels, array_diff(array('shd', 'hd', 'sd'), $levels));

            foreach ($levels as $level) {
                if (empty($beginnings[$level])) {
                    continue;
                }

                $beginning['beginningKey'] = $beginnings[$level]['key'];
                $token = $this->getTokenService()->makeToken('hls.clef', array(
                    'data' => array(
                        'id' => $file['id'],
                        'level' => $level,
                        'fromApi' => $params['fromApi'],
                    ),
                    'times' => $this->agentInWhiteList($request->headers->get('user-agent')) ? 0 : 1,
                    'duration' => 3600,
                    'userId' => $params['userId'],
                ));

                $beginning['beginningKeyUrl'] = $this->generateUrl("hls_{$this->getRoutingPrefix()}clef", array(
                    'id' => $file['id'],
                    'token' => $token['token'],
                ), true);
                break;
            }
        }

        return $beginning;
    }

    protected function getFile($fileId, $token)
    {
        if (empty($fileId) && !empty($token['data']['replayId'])) {
            $replay = $this->getLiveReplayService()->getReplay($token['data']['replayId']);
            $file = $file = $this->getCloudFileService()->getByGlobalId($replay['globalId']);
        } else {
            $file = $this->getUploadFileService()->getFullFile($fileId);
        }

        return $file;
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->getBiz()->service('File:UploadFileService');
    }

    /**
     * @return TokenService
     */
    protected function getTokenService()
    {
        return $this->getBiz()->service('User:TokenService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }

    protected function getLiveReplayService()
    {
        return $this->getBiz()->service('Course:LiveReplayService');
    }

    protected function getCloudFileService()
    {
        return $this->getBiz()->service('CloudFile:CloudFileService');
    }
}
