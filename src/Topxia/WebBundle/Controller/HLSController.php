<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Service\Util\CloudClientFactory;
use Topxia\Common\StringToolkit;
use Topxia\Common\FileToolkit;
use Topxia\Service\User\CurrentUser;
use Topxia\Service\CloudPlatform\CloudAPIFactory;

class HLSController extends BaseController
{
    public function getStrAction(Request $request)
    {
        $array = array(
            array(
              'level' => 'hls-sd',
              'src' => 'http://drools3.qiniudn.com/20140810025415/sa0xxqdbc6scg04s89h9hiui/69202eb0d1205f66_sd.m3u8?' . time(),
              'name' => 'SD'
            ),
            array(
              'level' => 'hls-hd',
              'src' => 'http://drools3.qiniudn.com/20140810025415/sa0xxqdbc6scg04s89h9hiui/69202eb0d1205f66_md.m3u8?' . time(),
              'name' => 'HD'
            ),
            array(
              'level' => 'hls-shd',
              'src' => 'http://drools3.qiniudn.com/20140810025415/sa0xxqdbc6scg04s89h9hiui/69202eb0d1205f66_hd.m3u8?' . time(),
              'name' => 'SHD'
            ) 
        );

        $levelName = $request->query->get('level');
        foreach ($array as $key => $value) {
            if($value["level"] == 'hls-'.$levelName) {
                $url = $value["src"];
            }
        }

        return new Response($url, 200);
    }

    public function playlistAction(Request $request, $id, $token)
    {
        $levelParam = $request->query->get('level', null);

        if(!empty($levelParam) && !in_array($levelParam, array('HD','SHD','SD'))){
            throw $this->createNotFoundException();
        }

        $line = $request->query->get('line', null);
        $hideBeginning = $request->query->get('hideBeginning', false);
        $token = $this->getTokenService()->verifyToken('hls.playlist', $token);

        if (empty($token)) {
            throw $this->createNotFoundException();
        }

        $dataId = is_array($token['data']) ? $token['data']['id'] : $token['data'];
        
        if ($dataId != $id) {
            throw $this->createNotFoundException();
        }

        $file = $this->getUploadFileService()->getFile($id);
        if (empty($file)) {
            throw $this->createNotFoundException();
        }

        $streams = array();
        $mode = is_array($token['data']) ? $token['data']['mode'] : '';

        foreach (array('sd', 'hd', 'shd') as $level) {
            if (empty($file['metas2'][$level])) {
                continue;
            }

            $token = $this->getTokenService()->makeToken('hls.stream', array('data' => array('id' => $file['id']. $level, 'mode' => $mode) , 'times' => 1, 'duration' => 3600));

            if(!empty($levelParam) && strtolower($levelParam) != $level) {
                $this->getTokenService()->verifyToken('hls.stream', $token["token"]);
            }

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

        $api = CloudAPIFactory::create();

        $playlist = $api->get('/hls/playlist/json', array( 'streams' => $streams, 'qualities' => $qualities));

        return $this->createJsonResponse($playlist);
    }

    public function streamAction(Request $request, $id, $level, $token)
    {
        $token = $this->getTokenService()->verifyToken('hls.stream', $token);

        if (empty($token)) {
            throw $this->createNotFoundException();
        }

        $dataId = is_array($token['data']) ? $token['data']['id'] : $token['data'];
        if ($dataId != ($id.$level)) {
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

        $mode = is_array($token['data']) ? $token['data']['mode'] : '';
        $timelimit = $this->setting('magic.lesson_watch_time_limit');
        if ($mode == 'preview' && !empty($timelimit)) {
            $params['limitSecond'] = $timelimit;
        }

        $token = $this->getTokenService()->makeToken('hls.clef', array('data' => array('id' => $file['id'], 'mode' => $mode), 'times' => 1, 'duration' => 3600));
        $params['keyUrl'] = $this->generateUrl('hls_clef', array('id' =>  $file['id'], 'token' => $token['token']), true);

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
        
        $api = CloudAPIFactory::create();
        
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

        $dataId = is_array($token['data']) ? $token['data']['id'] : $token['data'];
        if ($dataId != $id) {
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

    protected function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File.UploadFileService');
    }

    protected function getTokenService()
    {
        return $this->getServiceKernel()->createService('User.TokenService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getVideoBeginning($level)
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

}