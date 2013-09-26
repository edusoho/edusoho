<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class AuthController extends BaseController
{
    public function emailConfirmAction(Request $request)
    {
        $token = $this->getUserService()->getToken('email-verify', $request->query->get('token'));
        if (empty($token)) {
            return $this->render('TopxiaWebBundle:Auth:email-confirm-error.html.twig');
        }

        $user = $this->getUserService()->getUser($token['userId']);
        if (empty($user)) {
            return $this->render('TopxiaWebBundle:Auth:email-confirm-error.html.twig');
        }

        if ($token['data']) {
            $this->getUserService()->changeEmail($user['id'], $token['data']);
        }

        $this->getUserService()->setEmailVerified($user['id']);

        $this->getUserService()->deleteToken('email-verify', $token['token']);

        $this->authenticateUser($this->getUserService()->getUser($user['id']));

        return $this->redirect($this->generateUrl('homepage'));
    }
}