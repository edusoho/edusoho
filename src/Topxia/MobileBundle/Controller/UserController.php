<?php

namespace Topxia\MobileBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\BaseController;

class UserController extends BaseController
{
    public function getUserAction(Request $request, $id)
    {
        $user = $this->getUserService()->getUser($id);
        return $this->createJsonResponse($user);
    }
}
