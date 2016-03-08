<?php

namespace MaterialLib\MaterialLibBundle\Controller\Admin;

use Topxia\Common\Paginator;
use Topxia\Service\Util\CloudClientFactory;
use Symfony\Component\HttpFoundation\Request;
use MaterialLib\MaterialLibBundle\Controller\BaseController;

class MaterialLibController extends BaseController
{
    public function indexAction()
    {
        return $this->redirect($this->generateUrl('admin_material_lib_manage'));
    }

    public function manageAction(Request $request)
    {
        return $this->render('MaterialLibBundle:Admin:manage.html.twig', array(
            'type'          => $request->query->get('type', ''),
            'courseId'      => $request->query->get('courseId', ''),
            'createdUserId' => $request->query->get('createdUserId', '')
        ));
    }

    public function renderAction(Request $request)
    {
        $conditions = $request->query->all();
        $results    = $this->getMaterialLibService()->search(
            $conditions,
            ($request->query->get('page', 1) - 1) * 20,
            20
        );
        $paginator = new Paginator(
            $this->get('request'),
            $results['count'],
            20
        );

        return $this->render('MaterialLibBundle:Admin:tbody.html.twig', array(
            'type'         => empty($conditions['type']) ? 'all' : $conditions['type'],
            'materials'    => $results['data'],
            'createdUsers' => $results['createdUsers'],
            'paginator'    => $paginator
        ));
    }

    public function detailAction(Request $reqeust, $globalId)
    {
        $material   = $this->getMaterialLibService()->get($globalId);
        $thumbnails = $this->getMaterialLibService()->getDefaultHumbnails($globalId);
        return $this->render('MaterialLibBundle:Web:detail.html.twig', array(
            'material'   => $material,
            'thumbnails' => $thumbnails,
            'params'     => $reqeust->query->all()
        ));
    }

    public function editAction(Request $request, $globalId)
    {
        $fields = $request->request->all();
        $this->getMaterialLibService()->edit($globalId, $fields);
        return $this->createJsonResponse(array('success' => true));
    }

    public function deleteAction($globalId)
    {
        $this->getMaterialLibService()->delete($globalId);
        return $this->createJsonResponse(array('success' => true));
    }

    public function downloadAction($globalId)
    {
        $download = $this->getMaterialLibService()->download($globalId);
        return $this->redirect($download['url']);
    }

    public function statsAction(Request $request)
    {
        $space = array(
            array('name' => '视频', 'value' => 128, 'per' => '11.28%'),
            array('name' => '音频', 'value' => 98, 'per' => '8.63%'),
            array('name' => '图片', 'value' => 278, 'per' => '24.49%'),
            array('name' => '文档', 'value' => 118, 'per' => '10.4%'),
            array('name' => 'PPT', 'value' => 54, 'per' => '4.76%'),
            array('name' => '其他', 'value' => 459, 'per' => '40.44%')
        );

        $flow = array(
            array('name' => '视频', 'value' => 128, 'per' => '11.28%'),
            array('name' => '音频', 'value' => 98, 'per' => '8.63%'),
            array('name' => '图片', 'value' => 278, 'per' => '24.49%'),
            array('name' => '文档', 'value' => 118, 'per' => '10.4%'),
            array('name' => 'PPT', 'value' => 54, 'per' => '4.76%'),
            array('name' => '其他', 'value' => 459, 'per' => '40.44%')
        );

        $date  = array();
        $data1 = array();
        $data2 = array();
        for ($i = 1; $i <= 365; $i++) {
            $inter   = $i + 1;
            $date[]  = date('Y-m-d', strtotime("+{$i} day"));
            $data1[] = rand(0, 100);
            $data2[] = rand(0, 200);
        }

        $stats = $this->getMaterialLibService()->getStatistics();
        return $this->render('MaterialLibBundle:Admin:stats.html.twig', array(
            'stats' => $stats,
            'space' => $space,
            'flow'  => $flow,
            'date'  => $date,
            'data1' => $data1,
            'data2' => $data2
        ));
    }

    public function playAction(Request $request, $globalId)
    {
        $file = $this->getMaterialLibService()->get($globalId);

        if (empty($file)) {
            throw $this->createNotFoundException();
        }

        if ($file['type'] == 'video') {
            if (!empty($file['convertParams']['hasVideoWatermark'])) {
                $file['videoWatermarkEmbedded'] = 1;
            }

            $player = "balloon-cloud-video-player";
        } elseif ($file['type'] == 'audio') {
            $player = "audio-player";
        } else {
            throw new Exception("Error File Type.");
        }

        $url = $this->getPlayUrl($globalId, array());

        return $this->render('TopxiaWebBundle:Player:show.html.twig', array(
            'file'             => $file,
            'url'              => $url,
            'context'          => array(),
            'player'           => $player,
            'agentInWhiteList' => $this->agentInWhiteList($request->headers->get("user-agent"))
        ));
    }

    protected function getPlayUrl($globalId, $context)
    {
        $file = $this->getMaterialLibService()->get($globalId);

        if (empty($file)) {
            throw $this->createNotFoundException();
        }

        if (!in_array($file["type"], array("audio", "video"))) {
            throw $this->createAccessDeniedException();
        }

        $factory = new CloudClientFactory();
        $client  = $factory->createClient();

        if (!empty($file['metas']) && !empty($file['metas']['levels']['sd']['key'])) {
            // if (isset($file['convertParams']['convertor']) && ($file['convertParams']['convertor'] == 'HLSEncryptedVideo')) {
            $token = $this->makeToken('hls.playlist', $file['id'], $context);

            $params = array(
                'id'    => $file['id'],
                'token' => $token['token']
            );

            return $this->generateUrl('hls_playlist', $params, true);
            // } else {
            //     $result = $client->generateHLSQualitiyListUrl($file['metas'], 3600);
            // }
        } else {
            if (!empty($file['metas']) && !empty($file['metas']['hd']['key'])) {
                $key = $file['metas']['hd']['key'];
            } else {
                $key = $file['reskey'];
            }

            if ($key) {
                $result = $client->generateFileUrl($client->getBucket(), $key, 3600);
            }
        }

        return $result['url'];
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

    protected function getTokenService()
    {
        return $this->getServiceKernel()->createService('User.TokenService');
    }

    protected function getMaterialLibService()
    {
        return $this->createService('MaterialLib:MaterialLib.MaterialLibService');
    }
}
