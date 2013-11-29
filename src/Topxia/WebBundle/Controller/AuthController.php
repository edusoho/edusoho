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

        $newEmail = $token['data'];
        if (empty($newEmail)) {
            return $this->render('TopxiaWebBundle:Auth:email-confirm-error.html.twig');
        }

        if ($request->getMethod() == 'POST') {

            $data = $request->request->all();

            $isPasswordOk = $this->getAuthService()->checkPassword($user['id'], $data['password']);
            if (!$isPasswordOk) {
                $this->setFlashMessage('danger', '密码不正确，请重试。');
                return $this->redirect($this->generateUrl('auth_email_confirm', array('token' => $token['token'])));
            }

            $this->getAuthService()->changeEmail($user['id'], $data['password'], $newEmail);

            $this->getUserService()->setEmailVerified($user['id']);

            $this->getUserService()->deleteToken('email-verify', $token['token']);

            $this->authenticateUser($this->getUserService()->getUser($user['id']));
            return $this->redirect($this->generateUrl('homepage'));
        }

        return $this->render('TopxiaWebBundle:Auth:email-confirm.html.twig', array(
            'newEmail' => $newEmail,

        ));
    }

    private function getAuthService()
    {
        return $this->getServiceKernel()->createService('User.AuthService');
    }
}