<?php
namespace Topxia\WebBundle\Controller;

use Exception;
use Symfony\Component\HttpFoundation\Request;

class UserController extends BaseController
{

    public function unfollowAction(Request $request, $id)
    {
        try {
            $user = $this->getCurrentUser();
            $this->getUserService()->unFollow($user['id'], $id);
        } catch (Exception $e) {
            return $this->createJsonResponse(false);
        }
        return $this->createJsonResponse(true);
    }

    public function followAction(Request $request, $id)
    {
        try {
            $user = $this->getCurrentUser();
            $this->getUserService()->follow($user['id'], $id);
        } catch (Exception $e) {
            return $this->createJsonResponse(false);
        }
        return $this->createJsonResponse(true);
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

}