<?php

namespace Topxia\AdminBundle\Controller;

use Topxia\Common\Paginator;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\CloudPlatform\CloudAPIFactory;
use MaterialLib\MaterialLibBundle\Controller\BaseController;

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

        if (isset($result['hasStorage']) && $result['hasStorage'] == '1') {
            return $this->redirect($this->generateUrl('admin_cloud_file_manage'));
        }

        return $this->render('TopxiaAdminBundle:CloudFile:error.html.twig', array());
    }

    public function manageAction(Request $request)
    {
        return $this->render('TopxiaAdminBundle:CloudFile:manage.html.twig', array(
            'tags' => $this->getTagService()->findAllTags(0, PHP_INT_MAX)
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

        return $this->render('TopxiaAdminBundle:CloudFile:tbody.html.twig', array(
            'type'         => empty($conditions['type']) ? 'all' : $conditions['type'],
            'materials'    => $results['data'],
            'createdUsers' => $results['createdUsers'],
            'paginator'    => $paginator
        ));
    }

    public function detailAction(Request $reqeust, $globalId)
    {
        try {
            $material = $this->getMaterialLibService()->get($globalId);

            if ($material['type'] == 'video') {
                $thumbnails = $this->getMaterialLibService()->getDefaultHumbnails($globalId);
            }
        } catch (\RuntimeException $e) {
            return $this->render('MaterialLibBundle:Web:detail-not-found.html.twig', array());
        }

        return $this->render('MaterialLibBundle:Web:detail.html.twig', array(
            'material'   => $material,
            'thumbnails' => empty($thumbnails) ? "" : $thumbnails,
            'params'     => $reqeust->query->all()
        ));
    }

    public function reconvertAction(Request $request, $globalId)
    {
        return $this->getMaterialLibService()->reconvert($globalId, array(
            'directives' => array()
        ));
    }

    public function playAction(Request $request, $globalId)
    {
        return $this->forward('MaterialLibBundle:GlobalFilePlayer:player', array(
            'globalId' => $globalId
        ));
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
