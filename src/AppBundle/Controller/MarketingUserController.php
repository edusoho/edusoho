<?php

namespace AppBundle\Controller;

use Biz\Classroom\Service\ClassroomService;
use Symfony\Component\HttpFoundation\Request;
use Biz\User\CurrentUser;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class MarketingUserController extends BaseController
{
    public function loginAction(Request $request)
    {
        $ticket = $request->query->get('ticket');
        $token = $this->getTokenService()->verifyToken('marketing_login',$ticket);
        if ($token !== false) {
            $user = $this->getUserService()->getUser($token['userId']);
            if (empty($user)) {
                return $this->createResourceNotFoundException('user',$token['userId']);
            }
            $this->authenticateUser($user);
            $url = $this->generateUrl('course_show',array('id'=>$token['data']['targetId']));
            return $this->redirect($url);
        }
        return $this->redirect($this->generateUrl('homepage'));

    }
   
      protected function getTokenService()
    {
        return $this->createService('User:TokenService');
    }
}
