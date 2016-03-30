<?php

namespace MaterialLib\MaterialLibBundle\Controller\Admin;

use Topxia\Common\Paginator;
use Topxia\Service\Util\CloudClientFactory;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\CloudPlatform\CloudAPIFactory;
use Topxia\Common\ArrayToolkit;
use MaterialLib\MaterialLibBundle\Controller\BaseController;

class MaterialLibController extends BaseController
{
    public function indexAction()
    {
        try{
          $api = CloudAPIFactory::create('root');
          $result = $api->get("/me");
        } catch (\RuntimeException $e) {
            return $this->render('MaterialLibBundle:Admin:api-error.html.twig', array());
        }
        if(isset($result['hasStorage']) && $result['hasStorage'] == '1' ){
          return $this->redirect($this->generateUrl('admin_material_lib_manage'));
        }
        return $this->render('MaterialLibBundle:Admin:error.html.twig', array());
    }

    public function manageAction(Request $request)
    {
        $conditions = $request->query->all();

        return $this->render('MaterialLibBundle:Admin:manage.html.twig', array(
            'type'          => $request->query->get('type', ''),
            'courseId'      => $request->query->get('courseId', ''),
            'createdUserId' => $request->query->get('createdUserId', ''),
            'tags'          => $this->getTagService()->findAllTags(0, PHP_INT_MAX)
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
        $results  = $this->getMaterialLibService()->filterTagCondition($conditions,$results);

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

    public function reconvertAction(Request $request, $globalId)
    {
      return $this->getMaterialLibService()->reconvert($globalId,array());
    }

    public function playAction(Request $request, $globalId)
    {
        return $this->forward('MaterialLibBundle:GlobalFilePlayer:player', array(
            'globalId' => $globalId
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
        return $this->createService('User.TokenService');
    }

    protected function getTagService()
    {
        return $this->createService('Taxonomy.TagService');
    }

    protected function getMaterialLibService()
    {
        return $this->createService('MaterialLib:MaterialLib.MaterialLibService');
    }

}
