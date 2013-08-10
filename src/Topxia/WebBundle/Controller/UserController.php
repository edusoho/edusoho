<?php
namespace Topxia\WebBundle\Controller;

use Exception;
use Symfony\Component\HttpFoundation\Request;

class UserController extends BaseController
{

    public function remindCounterAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $counter = array('newMessageNum' => 0, 'newNotificationNum' => 0);
        if ($user->isLogin()) {
            $counter['newMessageNum'] = $user['newMessageNum'];
            $counter['newNotificationNum'] = $user['newNotificationNum'];
        }
        return $this->createJsonResponse($counter);
    }

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