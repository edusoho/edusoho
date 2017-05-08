<?php

namespace AppBundle\Controller;

use Biz\User\CurrentUser;
use Biz\User\Service\AuthService;
use Biz\User\Service\TokenService;
use Biz\User\Service\UserService;
use Symfony\Component\HttpFoundation\Request;

class AuthController extends BaseController
{
    public function emailConfirmAction(Request $request)
    {
        $token = $this->getTokenService()->verifyToken('email-verify', $request->query->get('token'));

        if (empty($token)) {
            return $this->render('auth/email-confirm-error.html.twig');
        }

        $user = $this->getUserService()->getUser($token['userId']);

        if (empty($user)) {
            return $this->render('auth/email-confirm-error.html.twig');
        }

        $newEmail = $token['data'];

        if (empty($newEmail)) {
            return $this->render('auth/email-confirm-error.html.twig');
        }

        if ($request->getMethod() == 'POST') {
            $data = $request->request->all();

            $isPasswordOk = $this->getAuthService()->checkPassword($user['id'], $data['password']);
            if (!$isPasswordOk) {
                $this->setFlashMessage('danger', 'site.incorrect.password');

                return $this->redirect($this->generateUrl('auth_email_confirm', array('token' => $token['token'])));
            }

            $this->getAuthService()->changeEmail($user['id'], $data['password'], $newEmail);

            $this->getUserService()->setEmailVerified($user['id']);

            $this->getTokenService()->destoryToken($token['token']);

            $user['currentIp'] = $this->container->get('request')->getClientIp();
            $currentUser = new CurrentUser();
            $currentUser->fromArray($user);

            $this->switchUser($request, $currentUser);

            return $this->redirect($this->generateUrl('homepage'));
        }

        return $this->render('auth/email-confirm.html.twig', array(
            'newEmail' => $newEmail,
        ));
    }

    /**
     * @return AuthService
     */
    protected function getAuthService()
    {
        return $this->getBiz()->service('User:AuthService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
    }

    /**
     * @return TokenService
     */
    protected function getTokenService()
    {
        return $this->getBiz()->service('User:TokenService');
    }
}
