<?php
namespace Classroom\ClassroomBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\AdminBundle\Controller\BaseController;

class ClassroomOrderAdminController extends BaseController
{
    public function manageAction(Request $request)
    {
        return $this->forward('TopxiaAdminBundle:Order:manage', array(
            'request' => $request,
            'targetType' => 'classroom',
        ));
    }
}
