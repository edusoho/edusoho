<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class MarketingUserController extends BaseController
{
    public function loginAction(Request $request)
    {
        $ticket = $request->query->get('ticket');
        $token = $this->getTokenService()->verifyToken('marketing_login', $ticket);
        if ($token !== false) {
            $user = $this->getUserService()->getUser($token['userId']);
            if (empty($user)) {
                return $this->createResourceNotFoundException('user', $token['userId']);
            }
            $this->authenticateUser($user);
            $url = $this->generateUrl('course_show', array('id' => $token['data']['targetId']));
            $request->getSession()->set('_target_path', $url);

            return $this->redirect($url);
        }

        return $this->redirect($this->generateUrl('homepage'));
    }

    protected function getTokenService()
    {
        return $this->createService('User:TokenService');
    }
}
