<?php
namespace Topxia\WebBundle\Controller;

use Topxia\Common\FileToolkit;
use Topxia\Service\Util\CloudClientFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Service\CloudPlatform\CloudAPIFactory;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PlayerController extends BaseController
{
    public function showAction(Request $request, $id, $context = array())
    {
        // todo delete try catche
        try {
            $file = $this->getUploadFileService()->getFullFile($id);

            if (empty($file)) {
                throw $this->createNotFoundException();
            }

            $agentInWhiteList = $this->agentInWhiteList($request->headers->get("user-agent"));
            if ($file["storage"] == 'cloud' && $file["type"] == 'video') {
                // 加入片头信息
                if (!$this->isHiddenVideoHeader()) {
                    $videoHeaderFile = $this->getUploadFileService()->getFileByTargetType('headLeader');
                    if (!empty($videoHeaderFile) && $videoHeaderFile['convertStatus'] == 'success') {
                        $context['videoHeaderLength'] = $videoHeaderFile['length'];
                    }
                }

                if (!empty($file['convertParams']['hasVideoWatermark'])) {
                    $file['videoWatermarkEmbedded'] = 1;
                }

                $player = "balloon-cloud-video-player";

                $api    = CloudAPIFactory::create("leaf");
                $result = $api->get("/resources/{$file['globalId']}/player");

                // 临时修复手机浏览器端视频不能播放的问题
                if ($agentInWhiteList) {
                    $context['hideQuestion'] = 1; //手机浏览器不弹题
                    if (isset($file['mcStatus']) && $file['mcStatus'] == 'yes') {
                        $player = "local-video-player";
                        $url    = isset($result['mp4url']) ? $result['mp4url'] : '';
                    }
                }
            } elseif ($file["storage"] == 'local' && $file["type"] == 'video') {
                $player = "local-video-player";
            } elseif ($file["type"] == 'audio') {
                $player = "audio-player";
            }

            $url = $this->getPlayUrl($id, $context);
        } catch (\Exception $e) {
        }
        return $this->render('TopxiaWebBundle:Player:show.html.twig', array(
            'file'             => $file,
            'url'              => isset($url) ? $url : null,
            'context'          => $context,
            'player'           => $player,
            'agentInWhiteList' => $agentInWhiteList
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

        $playlist = $api->get('/hls/playlist/json', array('streams' => $streams, 'qualities' => $qualities));
        return $this->createJsonResponse($playlist);
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
        $params['fileId'] = $file['id'];

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

        $file = $this->getMaterialLibService()->get($globalId);

        if (empty($file)) {
            return $this->makeFakeTokenString();
        }

        if (empty($file['metas']['levels'][$token['data']['level']]['hlsKey'])) {
            return $this->makeFakeTokenString();
        }

        return new Response($file['metas']['levels'][$token['data']['level']]['hlsKey']);
    }

    protected function getPlayUrl($id, $context)
    {
        $file = $this->getUploadFileService()->getFile($id);

        if (empty($file)) {
            throw $this->createNotFoundException();
        }

        if (!in_array($file["type"], array("audio", "video"))) {
            throw $this->createAccessDeniedException();
        }

        if ($file['storage'] == 'cloud') {
            if (!empty($file['globalId'])) {
                $file = $this->getUploadFileService()->getFullFile($file['id']);
            }

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
                'id'    => $id,
                'token' => $token['token']
            ));
        }
    }

    protected function makeToken($type, $fileId, $context = array())
    {
        $fileds = array(

            'data'     => array(
                'id' => $fileId
            ),
            'times'    => 3,
            'duration' => 3600,
            'userId'   => $this->getCurrentUser()->getId()
        );

        if (isset($context['watchTimeLimit'])) {
            $fileds['data']['watchTimeLimit'] = $context['watchTimeLimit'];
        }

        if (isset($context['hideBeginning'])) {
            $fileds['data']['hideBeginning'] = $context['hideBeginning'];
        }

        $token = $this->getTokenService()->makeToken($type, $fileds);

        return $token;
    }

    public function localMediaAction(Request $request, $id, $token)
    {
        $file = $this->getUploadFileService()->getFile($id);

        if (empty($file)) {
            throw $this->createNotFoundException();
        }

        if (!in_array($file["type"], array("audio", "video"))) {
            throw $this->createAccessDeniedException();
        }

        $token = $this->getTokenService()->verifyToken('local.media', $token);
        if (!$token || $token['userId'] != $this->getCurrentUser()->getId()) {
            throw $this->createAccessDeniedException();
        }

        $response = BinaryFileResponse::create($file['fullpath'], 200, array(), false);
        $response->trustXSendfileTypeHeader();

        $mimeType = FileToolkit::getMimeTypeByExtension($file['ext']);

        if ($mimeType) {
            $response->headers->set('Content-Type', $mimeType);
        }

        return $response;
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

    protected function getTokenService()
    {
        return $this->getServiceKernel()->createService('User.TokenService');
    }

    protected function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File.UploadFileService');
    }

    protected function getMaterialLibService()
    {
        return $this->getServiceKernel()->createService('MaterialLib:MaterialLib.MaterialLibService');
    }
}
