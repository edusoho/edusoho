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
        $line = $request->query->get('line', '');

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

            $streams[$level] = $this->generateUrl('hls_stream', array('id' => $file['id'], 'level' => $level, 'token' => $token['token'], 'line' => $line), true);
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

        $params = array(
            'key' => '',
            'keyUrl' => '',
            'headLeader'

        );


    }

    public function clefAction(Request $request, $id, $token)
    {
        $token = $this->getTokenService()->verifyToken('m3u8clef', $token);
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

    private function getHeadLeaderInfo()
    {
        $storage = $this->getSettingService()->get("storage");
        if(!empty($storage) && array_key_exists("video_header", $storage) && $storage["video_header"]){

            $headLeader = $this->getUploadFileService()->getFileByTargetType('headLeader');
            $headLeaderArray = json_decode($headLeader['metas2'],true);
            $headLeaders = array();
            foreach ($headLeaderArray as $key => $value) {
                $headLeaders[$key] = $value['key'];
            }
            $headLeaderHlsKeyUrl = $this->generateUrl('uploadfile_cloud_get_head_leader_hlskey', array(), true);

            return array(
                'headLeaders' => $headLeaders,
                'headLeaderHlsKeyUrl' => $headLeaderHlsKeyUrl,
                'headLength' => $headLeader['length']
            );
        } else {
            return array(
                'headLeaders' => '',
                'headLeaderHlsKeyUrl' => '',
                'headLength' => 0
            );
        }
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