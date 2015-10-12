<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\AdminBundle\Controller\BaseController;

class CourseOrderController extends BaseController
{
    public function manageAction(Request $request)
    {
        return $this->forward('TopxiaAdminBundle:Order:manage', array(
            'request' => $request,
            'targetType' => 'course',
            'layout' => 'TopxiaAdminBundle:CourseOrder:order.html.twig',
        ));
    }

}