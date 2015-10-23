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

    public function playlistAction(Request $request, $id, $token)
    {
        $line = $request->query->get('line', null);
        $hideBeginning = $request->query->get('hideBeginning', false);
        $returnJson = $request->query->get('returnJson', false);
        $token = $this->getTokenService()->verifyToken('hls.playlist', $token);

        $levelParam = $request->query->get('level', "");

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

            if(empty($levelParam) || (!empty($levelParam) && strtolower($levelParam) == $level)) {
                $tokenFields = array(
                    'data' => array(
                        'id' => $file['id']. $level, 
                        'mode' => $mode
                    ) , 
                    'times' => 1, 
                    'duration' => 3600
                );

                if(!empty($token['userId'])) {
                    $tokenFields['userId'] = $token['userId'];
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
            if (!$this->haveHeadLeader()) {
                $params['hideBeginning'] = 1;
            }
            $streams[$level] = $this->generateUrl('hls_stream', $params, true);
        }

        $qualities = array(
            'video' => $file['convertParams']['videoQuality'],
            'audio' => $file['convertParams']['audioQuality'],
        );

        $api = CloudAPIFactory::create('leaf');

        if($returnJson){
            $playlist = $api->get('/hls/playlist/json', array( 'streams' => $streams, 'qualities' => $qualities));
            return $this->createJsonResponse($playlist);
        } else {
            $playlist = $api->get('/hls/playlist', array( 'streams' => $streams, 'qualities' => $qualities));
            if (empty($playlist['playlist'])) {
                return $this->createMessageResponse('error', '生成视频播放列表失败！');
            }

            return new Response($playlist['playlist'], 200, array(
                'Content-Type' => 'application/vnd.apple.mpegurl',
                'Content-Disposition' => 'inline; filename="playlist.m3u8"',
            ));
        }
    }

    protected function haveHeadLeader()
    {
        $storage = $this->setting("storage");
        if(!empty($storage) && array_key_exists("video_header", $storage) && $storage["video_header"]){
            return true;
        }
        return false;
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


        $tokenFields = array(
            'data' => array(
                'id' => $file['id'], 
                'mode' => $mode
            ), 
            'times' => 1, 
            'duration' => 3600
        );

        if(!empty($token['userId'])) {
            $tokenFields['userId'] = $token['userId'];
        }

        $token = $this->getTokenService()->makeToken('hls.clef', $tokenFields);

        $params['keyUrl'] = $this->generateUrl('hls_clef', array('id' =>  $file['id'], 'token' => $token['token']), true);

        $hideBeginning = $request->query->get('hideBeginning');
        if (empty($hideBeginning)) {
            $beginning = $this->getVideoBeginning($level, $token['userId']);
            if ($beginning['beginningKey']) {
                $params = array_merge($params, $beginning);
            }
        }

        $line = $request->query->get('line');
        if (!empty($line)) {
            $params['line'] = $line;
        }
        
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

    public function clefAction(Request $request, $id, $token)
    {
        $token = $this->getTokenService()->verifyToken('hls.clef', $token);
        $fakeKey = $this->getTokenService()->makeFakeTokenString(16);

        if (empty($token)) {
            return new Response($fakeKey);
        }

        if(!empty($token['userId'])) {
            if(!($this->getCurrentUser()->isLogin()
                && $this->getCurrentUser()->getId() == $token['userId'])) {
                return new Response($fakeKey);
            }
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

    protected function getVideoBeginning($level, $userId = 0)
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
                $token = $this->getTokenService()->makeToken('hls.clef', array('data' => $file['id'], 'times' => 1, 'duration' => 3600, 'userId'=>$userId));
                $beginning['beginningKeyUrl'] = $this->generateUrl('hls_clef', array('id' => $file['id'], 'token' => $token['token']), true);
                break;
            }
        }

        return $beginning;
    }

}