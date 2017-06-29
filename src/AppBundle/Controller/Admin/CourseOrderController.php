<?php

namespace AppBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;

class CourseOrderController extends BaseController
{
    public function manageAction(Request $request)
    {
        return $this->forward('AppBundle:Admin/Order:manage', array(
            'request' => $request,
            'targetType' => 'course',
            'layout' => 'admin/course-order/order.html.twig',
        ));
    }
}
