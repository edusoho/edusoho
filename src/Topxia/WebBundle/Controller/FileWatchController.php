<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\WebBundle\Controller\BaseController;
use Topxia\Service\CloudPlatform\CloudAPIFactory;

class FileWatchController extends BaseController
{
    public function indexAction(Request $request, $file)
    {
        return $this->render("TopxiaWebBundle:FileWatch:index.html.twig", array(
            'player' => $this->getFilePlayer($file),
        ));
    }

    public function modalBodyAction(Request $request, $file)
    {
        return $this->render("TopxiaWebBundle:FileWatch:modal-body.html.twig", array(
            'player' => $this->getFilePlayer($file),
        ));
    }

    public function downloadAction(Request $request, $file)
    {
        if (empty($file['id'])) {
            $file = $this->getUploadFileService()->getFile($file);
        }

        if (empty($file)) {
            return array('error' => 'not_found', 'message' => "文件#{$id}不存在，不能查看！");
        }

        $download = $this->getUploadFileService()->getDownloadFile($file['id']);
        if (!empty($download['error'])) {
            return $this->createMessageResponse('error', '该文件不能下载！');
        }

        if ($download['type'] == 'local') {
            // x-send-file
        } elseif ($download['type'] == 'url') {
            return $this->redirect($download['url']);
        }

        return $this->createMessageResponse('error', '获取下载地址失败!');
    }

    protected function getFilePlayer($file)
    {
        if (empty($file['id'])) {
            $file = $this->getUploadFileService()->getFile($file);
        }

        if (empty($file)) {
            return array('error' => 'not_found', 'message' => "文件#{$id}不存在，不能查看！");
        }

        $player = array();
        $player['type'] = $file['type'];

        if ($file['type'] == 'video' || $file['type'] == 'audio') {
            $token = $this->getTokenService()->makeToken('hls.playlist', array('data' => $file['id'], 'times' => 3, 'duration' => 3600));
            $url = array(
                'url' => $this->generateUrl('hls_playlist', array(
                    'id' => $file['id'],
                    'token' => $token['token'],
                ), true),
            );
            $player['hls_playlist'] = $url['url'];
        } elseif ($file['type'] == 'ppt') {
            $api = CloudAPIFactory::create();
            $result = $api->get(sprintf("/files/%s/player", $file['globalId']));
            if (empty($result['images'])) {
                return array('error' => 'fetch_metas_failed', 'message' => "获取文件#{$file['id']}信息失败，不能查看。");
            }

            $player['slides'] = json_encode($result['images']);

        } elseif ($file['type'] == 'flash') {
            $api = CloudAPIFactory::create();
            $result = $api->get(sprintf("/files/%s/player", $file['globalId']));
            $player['url'] = $result['url'];

        } elseif ($file['type'] == 'document') {
            $api = CloudAPIFactory::create();
            $result = $api->get(sprintf("/files/%s/player", $file['globalId']));

            if (empty($result['pdf']) or empty($result['swf'])) {
                return array('error' => 'fetch_metas_failed', 'message' => "获取文件#{$file['id']}信息失败，不能查看。");
            }

            $player['pdfUri'] = $result['pdf'];
            $player['swfUri'] = $result['swf'];

        } else {
            return array('error' => 'can_not_watch', 'message' => "当前文件#{$file['id']}类型，不能查看。");
        }

        return $player;
    }

    protected function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File.UploadFileService2');
    }

    protected function getTokenService()
    {
        return $this->getServiceKernel()->createService('User.TokenService');
    }
}
