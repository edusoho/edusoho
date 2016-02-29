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
        $conditions = $request->query->all();
        $paginator = new Paginator(
            $this->get('request'),
            $this->getMaterialLibService()->searchCount($conditions),
            20
        );
        $materials = $this->getMaterialLibService()->search(
            $conditions,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('MaterialLibBundle:Admin:manage.html.twig', array(
            'type' => empty($conditions['type'])?'all':$conditions['type'],
            'materials' => $materials,
            'paginator' => $paginator
        ));
    }

    protected function getMaterialLibService()
    {
        return $this->createService('MaterialLib:MaterialLib.MaterialLibService');
    }
}
