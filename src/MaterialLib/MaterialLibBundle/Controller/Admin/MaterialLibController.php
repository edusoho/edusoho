<?php

namespace MaterialLib\MaterialLibBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use MaterialLib\MaterialLibBundle\Controller\BaseController;
use Topxia\Common\Paginator;

class MaterialLibController extends BaseController
{
    public function indexAction()
    {
        return $this->redirect($this->generateUrl('admin_material_lib_manage'));
    }

    public function manageAction(Request $request)
    {
        return $this->render('MaterialLibBundle:Admin:manage.html.twig', array(
            'type' => $request->query->get('type', 'all'),
        ));
    }

    public function renderAction(Request $request)
    {
        $conditions = $request->query->all();
        $results = $this->getMaterialLibService()->search(
            $conditions,
            ($request->query->get('page', 1) -1) * 20,
            20
        );
        $paginator = new Paginator(
            $this->get('request'),
            $results['count'],
            20
        );

        return $this->render('MaterialLibBundle:Admin:tbody.html.twig', array(
            'type' => empty($conditions['type'])?'all':$conditions['type'],
            'materials' => $results['data'],
            'paginator' => $paginator
        ));
    }

    protected function getMaterialLibService()
    {
        return $this->createService('MaterialLib:MaterialLib.MaterialLibService');
    }
}
