<?php
namespace Classroom\ClassroomBundle\Controller\Classroom;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\BaseController;

class CategoryController extends BaseController
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
