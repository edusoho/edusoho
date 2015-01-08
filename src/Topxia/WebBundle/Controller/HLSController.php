<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Service\Util\CloudClientFactory;
use Topxia\Common\StringToolkit;
use Topxia\Common\FileToolkit;
use Topxia\Service\User\CurrentUser;
use Topxia\Service\CloudPlatform\Client\CloudAPI;

class HLSController extends BaseController
{

    public function playlistAction(Request $request, $id, $token)
    {
        $line = $request->query->get('line', null);
        $hideBeginning = $request->query->get('hideBeginning', false);

        $token = $this->getTokenService()->verifyToken('hls.playlist', $token);

        if (empty($token)) {
            throw $this->createNotFoundException();
        }

        if ($token['data'] != $id) {
            throw $this->createNotFoundException();
        }

        $file = $this->getUploadFileService()->getFile($id);
        if (empty($file)) {
            throw $this->createNotFoundException();
        }

        $streams = array();
        foreach (array('sd', 'hd', 'shd') as $level) {
            if (empty($file['metas2'][$level])) {
                continue;
            }

            $token = $this->getTokenService()->makeToken('hls.stream', array('data' => $file['id'] . $level , 'times' => 1, 'duration' => 3600));
            $params = array(
                'id' => $file['id'],
                'level' => $level,
                'token' => $token['token'], 
            );

            if ($line) {
                $params['line'] = $line;
            }

            if ($hideBeginning) {
                $params['hideBeginning'] = 1;
            }

            $streams[$level] = $this->generateUrl('hls_stream', $params, true);
        }

        $qualities = array(
            'video' => $file['convertParams']['videoQuality'],
            'audio' => $file['convertParams']['audioQuality'],
        );

        $api = $this->createAPIClient();

        $playlist = $api->get('/hls/playlist', array( 'streams' => $streams, 'qualities' => $qualities));

        if (empty($playlist['playlist'])) {
            return $this->createMessageResponse('error', '生成视频播放列表失败！');
        }

        return new Response($playlist['playlist'], 200, array(
            'Content-Type' => 'application/vnd.apple.mpegurl',
            'Content-Disposition' => 'inline; filename="playlist.m3u8"',
        ));
    }

    public function streamAction(Request $request, $id, $level, $token)
    {
        $token = $this->getTokenService()->verifyToken('hls.stream', $token);

        if (empty($token)) {
            throw $this->createNotFoundException();
        }

        if ($token['data'] != ($id.$level)) {
            throw $this->createNotFoundException();
        }

        $file = $this->getUploadFileService()->getFile($id);
        if (empty($file)) {
            throw $this->createNotFoundException();
        }

        if (empty($file['metas2'][$level]['key'])) {
            throw $this->createNotFoundException();
        }

        $params = array();
        $params['key'] = $file['metas2'][$level]['key'];

        $token = $this->getTokenService()->makeToken('hls.clef', array('data' => $file['id'], 'times' => 1, 'duration' => 3600));
        $params['keyUrl'] = $this->generateUrl('hls_clef', array('id' => $file['id'], 'token' => $token['token']), true);

        $hideBeginning = $request->query->get('hideBeginning');
        if (empty($hideBeginning)) {
            $beginning = $this->getVideoBeginning($level);
            if ($beginning['beginningKey']) {
                $params = array_merge($params, $beginning);
            }
        }

        $line = $request->query->get('line');
        if (!empty($line)) {
            $params['line'] = $line;
        }

        $api = $this->createAPIClient();

        $stream = $api->get('/hls/stream', $params);

        if (empty($stream['stream'])) {
            return $this->createMessageResponse('error', '生成视频播放地址失败！');
        }

        return new Response($stream['stream'], 200, array(
            'Content-Type' => 'application/vnd.apple.mpegurl',
            'Content-Disposition' => 'inline; filename="stream.m3u8"',
        ));

    }

    public function clefAction(Request $request, $id, $token)
    {
        $token = $this->getTokenService()->verifyToken('hls.clef', $token);
        $fakeKey = $this->getTokenService()->makeFakeTokenString(16);
        if (empty($token)) {
            return new Response($fakeKey);
        }

        if ($token['data'] != $id) {
            return new Response($fakeKey);
        }

        $file = $this->getUploadFileService()->getFile($id);
        if (empty($file)) {
            return new Response($fakeKey);
        }

        if (empty($file['convertParams']['hlsKey'])) {
            return new Response($fakeKey);
        }

        return new Response($file['convertParams']['hlsKey']);
    }

    private function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File.UploadFileService');
    }

    private function getTokenService()
    {
        return $this->getServiceKernel()->createService('User.TokenService');
    }

    private function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    private function getVideoBeginning($level)
    {
        $beginning = array(
            'beginningKey' => null,
            'beginningKeyUrl' => null,
        );

        $storage = $this->getSettingService()->get("storage");
        if(!empty($storage['video_header'])) {

            $file = $this->getUploadFileService()->getFileByTargetType('headLeader');
            $beginnings = json_decode($file['metas2'], true);
            $levels = array($level);
            $levels = array_merge($levels, array_diff(array('shd', 'hd', 'sd'), $levels));

            foreach ($levels as $level) {
                if (empty($beginnings[$level])) {
                    continue;
                }

                $beginning['beginningKey'] = $beginnings[$level]['key'];
                $token = $this->getTokenService()->makeToken('hls.clef', array('data' => $file['id'], 'times' => 1, 'duration' => 3600));
                $beginning['beginningKeyUrl'] = $this->generateUrl('hls_clef', array('id' => $file['id'], 'token' => $token['token']), true);
                break;
            }
        }

        return $beginning;
    }

    protected function createAPIClient()
    {
        $settings = $this->getServiceKernel()->createService('System.SettingService')->get('storage', array());
        return new CloudAPI(array(
            'accessKey' => empty($settings['cloud_access_key']) ? '' : $settings['cloud_access_key'],
            'secretKey' => empty($settings['cloud_secret_key']) ? '' : $settings['cloud_secret_key'],
            'apiUrl' => empty($settings['cloud_api_server']) ? '' : $settings['cloud_api_server'],
        ));
    }

}