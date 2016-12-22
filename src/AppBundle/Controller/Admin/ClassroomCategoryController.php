<?php
namespace AppBundle\Controller\Admin;

use Topxia\WebBundle\Controller\BaseController;

class ClassroomCategoryController extends BaseController
{
    public function indexAction()
    {
        return $this->forward('TopxiaAdminBundle:Category:embed', array(
            'group'  => 'classroom',
            'layout' => 'TopxiaAdminBundle::layout.html.twig',
            'menu' => 'admin_classroom_category_manage'
        ));
    }
}
