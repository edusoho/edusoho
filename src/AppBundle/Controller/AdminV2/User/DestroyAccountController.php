<?php

namespace AppBundle\Controller\AdminV2\User;

use AppBundle\Controller\AdminV2\BaseController;
use Biz\User\Service\UserService;
use Symfony\Component\HttpFoundation\Request;

class DestroyAccountController extends BaseController
{
    public function recordIndexAction(Request $request)
    {
        return $this->render('admin-v2/user/destroy-account/record-list.html.twig', array(
        ));
    }

    public function indexAction(Request $request)
    {
        return $this->render('admin-v2/user/destroy-account/destroyed-list.html.twig', array(
        ));
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }
}
