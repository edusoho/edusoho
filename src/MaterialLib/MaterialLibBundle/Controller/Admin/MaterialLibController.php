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
            'type' => $request->query->get('type', ''),
            'courseId' => $request->query->get('courseId', ''),
            'createdUserId' => $request->query->get('createdUserId', '')
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

    public function detailAction(Request $reqeust, $globalId)
    {
        $material = $this->getMaterialLibService()->get($globalId);
        return $this->render('MaterialLibBundle:Web:detail.html.twig', array(
            'material' => $material,
            'params' => $reqeust->query->all()
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

    protected function getMaterialLibService()
    {
        return $this->createService('MaterialLib:MaterialLib.MaterialLibService');
    }
}
