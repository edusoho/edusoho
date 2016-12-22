<?php
namespace AppBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use Topxia\AdminBundle\Controller\BaseController;

class ClassroomOrderAdminController extends BaseController
{
    public function manageAction(Request $request)
    {
        return $this->forward('AppBundle:Admin/Order:manage', array(
            'request' => $request,
            'targetType' => 'classroom',
        ));
    }
}
