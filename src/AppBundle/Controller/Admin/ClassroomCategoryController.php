<?php

namespace AppBundle\Controller\Admin;

class ClassroomCategoryController extends BaseController
{
    public function indexAction()
    {
        return $this->forward('AppBundle:Admin/Category:embed', array(
            'group' => 'classroom',
            'layout' => 'admin/layout.html.twig',
            'menu' => 'admin_classroom_category_manage',
        ));
    }
}
