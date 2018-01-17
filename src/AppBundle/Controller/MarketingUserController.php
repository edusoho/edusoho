<?php

namespace AppBundle\Controller;

use AppBundle\Common\Exception\InvalidArgumentException;
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
            if (!in_array($token['data']['targetType'], array('classroom', 'course'))) {
                throw new InvalidArgumentException('targetType is invalid');
            }
            $url = $this->generateUrl($token['data']['targetType'].'_show', array('id' => $token['data']['targetId']));
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
