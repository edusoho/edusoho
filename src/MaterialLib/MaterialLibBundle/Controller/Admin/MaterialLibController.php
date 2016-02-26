<?php

namespace MaterialLib\MaterialLibBundle\Controller\Admin;

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
        return $this->render('MaterialLibBundle:Admin:manage.html.twig');
    }
}