<?php

namespace Topxia\AdminBundle\Controller;

use Topxia\Common\Paginator;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Service\CloudPlatform\CloudAPIFactory;
use Symfony\Component\HttpFoundation\Request;

class CloudFileController extends BaseController
{
    public function indexAction()
    {
        try {
            $api    = CloudAPIFactory::create('leaf');
            $result = $api->get("/me");
        } catch (\RuntimeException $e) {
            return $this->render('TopxiaAdminBundle:CloudFile:api-error.html.twig', array());
        }

        $storageSetting = $this->getSettingService()->get('storage', array());

        if (isset($result['hasStorage']) && $result['hasStorage'] == '1' && $storageSetting['upload_mode'] == "cloud") {
            return $this->redirect($this->generateUrl('admin_cloud_file_manage'));
        }

        return $this->render('TopxiaAdminBundle:CloudFile:error.html.twig', array());
    }

    public function manageAction(Request $request)
    {
        $storageSetting = $this->getSettingService()->get('storage', array());

        if ($storageSetting['upload_mode'] != "cloud") {
            return $this->redirect($this->generateUrl('admin_cloud_file'));
        }

        return $this->render('TopxiaAdminBundle:CloudFile:manage.html.twig', array(
            'tags' => $this->getTagService()->findAllTags(0, PHP_INT_MAX)
        ));
    }

    public function renderAction(Request $request)
    {
        $conditions = $request->query->all();
        $results    = $this->getCloudFileService()->search(
            $conditions,
            ($request->query->get('page', 1) - 1) * 20,
            20
        );

        $paginator = new Paginator(
            $this->get('request'),
            $results['count'],
            20
        );

        return $this->render('TopxiaAdminBundle:CloudFile:tbody.html.twig', array(
            'type'         => empty($conditions['type']) ? 'all' : $conditions['type'],
            'materials'    => $results['data'],
            'createdUsers' => isset($results['createdUsers']) ?$results['createdUsers'] : array(),
            'paginator'    => $paginator
        ));
    }

    public function previewAction(Request $reqeust, $globalId)
    {
        $file = $this->getCloudFileService()->getByGlobalId($globalId);

        return $this->render('TopxiaAdminBundle:CloudFile:preview-modal.html.twig', array(
            'file' => $file
        ));
    }

    public function detailAction(Request $reqeust, $globalId)
    {
        try {
            if (!$globalId) {
                return $this->render('TopxiaAdminBundle:CloudFile:detail-not-found.html.twig', array());
            }

            $cloudFile = $this->getCloudFileService()->getByGlobalId($globalId);
        } catch (\RuntimeException $e) {
            return $this->render('TopxiaAdminBundle:CloudFile:detail-not-found.html.twig', array());
        }

        try {
            if ($cloudFile['type'] == 'video') {
                $thumbnails = $this->getCloudFileService()->getDefaultHumbnails($globalId);
            }
        } catch (\RuntimeException $e) {
            $thumbnails = array();
        }

        return $this->render('TopxiaAdminBundle:CloudFile:detail.html.twig', array(
            'material'   => $cloudFile,
            'thumbnails' => empty($thumbnails) ? "" : $thumbnails,
            'params'     => $reqeust->query->all(),
            'editUrl'    => $this->generateUrl('admin_cloud_file_edit',array('globalId'=>$globalId))
        ));
    }

    public function editAction(Request $request, $globalId, $fields)
    {
        $fields = $request->request->all();

        $result = $this->getCloudFileService()->edit($globalId, $fields);
        return $this->createJsonResponse($result);
    }

    public function reconvertAction(Request $request, $globalId)
    {
        $cloudFile = $this->getCloudFileService()->reconvert($globalId, array(
            'directives' => array()
        ));

        if (isset($cloudFile['createdUserId'])) {
            $createdUser = $this->getUserService()->getUser($cloudFile['createdUserId']);
        }

        return $this->render('TopxiaAdminBundle:CloudFile:table-tr.html.twig', array(
            'cloudFile'   => $cloudFile,
            'createdUser' => isset($createdUser) ? $createdUser : array()
        ));
    }

    public function downloadAction($globalId)
    {
        $download = $this->getCloudFileService()->download($globalId);
        return $this->redirect($download['url']);
    }

    public function deleteAction($globalId)
    {
        $result = $this->getCloudFileService()->delete($globalId);
        return $this->createJsonResponse($result);
    }

    public function batchDeleteAction(Request $request)
    {
        $data = $request->request->all();

        if (isset($data['ids']) && !empty($data['ids'])) {

            $this->getCloudFileService()->batchDelete($data['ids']);
            return $this->createJsonResponse(true);
        }

        return $this->createJsonResponse(false);
    }

    public function batchTagShowAction(Request $request)
    {
        $data    = $request->request->all();
        $fileIds = preg_split('/,/', $data['fileIds']);

        $this->getMaterialLibService()->batchTagEdit($fileIds, $data['tags']);
        return $this->redirect($this->generateUrl('admin_cloud_file_manage'));
    }

    public function playerAction(Request $request, $globalId)
    {
        $file = $this->getCloudFileService()->getByGlobalId($globalId);

        if (empty($file)) {
            throw $this->createNotFoundException();
        }

        if ($file['type'] == 'video') {
            return $this->videoPlayer($file, $request);
        } elseif ($file['type'] == 'ppt') {
            return $this->render('TopxiaAdminBundle:CloudFile/Player:ppt-player.html.twig', array(
                'file' => $file
            ));
        } elseif ($file["type"] == 'audio') {
            return $this->audioPlayer($file);
        } elseif ($file["type"] == 'document') {
            return $this->render('TopxiaAdminBundle:CloudFile/Player:document-player.html.twig', array(
                'file' => $file
            ));
        } elseif ($file["type"] == 'image') {
            $file = $this->getCloudFileService()->download($file['no']);
            return $this->render('TopxiaAdminBundle:CloudFile/Player:image-player.html.twig', array(
                'file' => $file
            ));
        } elseif ($file["type"] == 'flash') {
            $file = $this->getCloudFileService()->player($file['no']);
            return $this->render('TopxiaAdminBundle:CloudFile/Player:flash-player.html.twig', array(
                'file' => $file
            ));
        }
    }

    public function pptAction(Request $request, $globalId)
    {
        $file = $this->getCloudFileService()->player($globalId);

        if (empty($file)) {
            throw $this->createNotFoundException();
        }

        return $this->createJsonResponse($file);
    }

    public function documentAction(Request $request, $globalId)
    {
        $file = $this->getCloudFileService()->player($globalId);

        if (empty($file)) {
            throw $this->createNotFoundException();
        }

        return $this->createJsonResponse($file);
    }

    public function audioPlayer($file)
    {
        $result = $this->getCloudFileService()->player($file['no']);
        return $this->render('TopxiaAdminBundle:CloudFile/Player:global-video-player.html.twig', array(
            'file'             => $file,
            'url'              => $result['url'],
            'player'           => 'audio-player',
            'agentInWhiteList' => $this->agentInWhiteList($this->getRequest()->headers->get("user-agent"))
        ));
    }

    protected function videoPlayer($file, $request)
    {
        $url = $this->getPlayUrl($file);

        return $this->render('TopxiaAdminBundle:CloudFile/Player:global-video-player.html.twig', array(
            'file'             => $file,
            'url'              => $url,
            'player'           => 'balloon-cloud-video-player',
            'params'           => $request->query->all(),
            'agentInWhiteList' => $this->agentInWhiteList($this->getRequest()->headers->get("user-agent"))
        ));
    }

    protected function getPlayUrl($file)
    {
        if (!in_array($file["type"], array("audio", "video"))) {
            throw $this->createAccessDeniedException();
        }

        $token = $this->makeToken('hls.playlist', $file['no']);

        $params = array(
            'globalId' => $file['no'],
            'token'    => $token['token']
        );

        return $this->generateUrl('global_file_hls_playlist', $params, true);
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

        $file = $this->getCloudFileService()->getByGlobalId($globalId);

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

        $file = $this->getCloudFileService()->getByGlobalId($globalId);

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
        $params['fileId'] = $file['extno'];

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

        $file = $this->getCloudFileService()->getByGlobalId($globalId);

        if (empty($file)) {
            return $this->makeFakeTokenString();
        }

        if (empty($file['metas']['levels'][$token['data']['level']]['hlsKey'])) {
            return $this->makeFakeTokenString();
        }

        return new Response($file['metas']['levels'][$token['data']['level']]['hlsKey']);
    }

    protected function makeToken($type, $globalId)
    {
        $fileds = array(
            'data'     => array(
                'globalId' => $globalId
            ),
            'times'    => 3,
            'duration' => 3600,
            'userId'   => $this->getCurrentUser()->getId()
        );

        $token = $this->getTokenService()->makeToken($type, $fileds);
        return $token;
    }

    protected function createService($service)
    {
        return $this->getServiceKernel()->createService($service);
    }

    protected function getTokenService()
    {
        return $this->createService('User.TokenService');
    }

    protected function getSettingService()
    {
        return $this->createService('System.SettingService');
    }

    protected function getTagService()
    {
        return $this->createService('Taxonomy.TagService');
    }

    protected function getCloudFileService()
    {
        return $this->createService('CloudFile.CloudFileService');
    }

    protected function getUploadFileService()
    {
        return $this->createService('File.UploadFileService');
    }

    protected function getMaterialLibService()
    {
        return $this->createService('MaterialLib:MaterialLib.MaterialLibService');
    }
}
