<?php

namespace AppBundle\Controller\OAuth2;

use AppBundle\Component\RateLimit\LoginFailRateLimiter;
use AppBundle\Controller\LoginBindController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LoginController extends LoginBindController
{
    public function mainAction(Request $request)
    {
        $oauthUser = $this->getOauthUser($request);

        return $this->render('oauth2/index.html.twig', array(
            'oauthUser' => $oauthUser,
        ));
    }

    public function appAction(Request $request)
    {
        $accessToken = $request->query->get('access_token');
        $openid = $request->query->get('openid');
        $type = $request->query->get('type');

        $client = $this->createOAuthClient($type);
        $oUser = $client->getUserInfo($this->makeFakeToken($type, $accessToken, $openid));

        $this->storeOauthUserToSession($request, $oUser, $type, true);

        return $this->redirect($this->generateUrl('oauth2_login_index'));
    }

    private function makeFakeToken($type, $accessToken, $openid)
    {
        switch ($type) {
            case 'weibo':
                $token = array(
                    'uid' => $openid,
                    'access_token' => $accessToken,
                );
                break;
            case 'qq':
                $token = array(
                    'openid' => $openid,
                    'access_token' => $accessToken,
                );
                break;
            case 'weixinweb':
                $token = array(
                    'openid' => $openid,
                    'access_token' => $accessToken,
                );
                break;
            default:
                throw new BadRequestHttpException('Bad type');
        }

        return $token;
    }

    public function bindAccountAction(Request $request)
    {
        $oauthUser = $this->getOauthUser($request);

        $type = $request->request->get('accountType');
        $account = $request->request->get('account');

        $user = $this->getUserByTypeAndAccount($type, $account);
        $oauthUser->accountType = $type;
        $oauthUser->account = $account;

        if ($user) {
            $redirectUrl = $this->generateUrl('oauth2_login_bind_login');
        } else {
            $redirectUrl = $this->generateUrl('oauth2_login_create');
        }

        $request->getSession()->set('oauth_user', $oauthUser);

        return $this->redirect($redirectUrl);
    }

    public function bindLoginAction(Request $request)
    {
        $oauthUser = $this->getOauthUser($request);
        if ('POST' == $request->getMethod()) {
            $password = $request->request->get('password');

            $this->loginAttemptCheck($oauthUser->account, $request);

            $isSuccess = $this->bindUser($oauthUser, $password);

            return $isSuccess ?
                $this->createSuccessJsonResponse(array('url' => $this->generateUrl('oauth2_login_success', array('isCreate' => 0)))) :
                $this->createFailJsonResponse(array('message' => $this->trans('user.settings.security.password_modify.incorrect_password')));
        } else {
            $user = $this->getUserByTypeAndAccount($oauthUser->accountType, $oauthUser->account);

            return $this->render('oauth2/bind-login.html.twig', array(
                'oauthUser' => $oauthUser,
                'esUser' => $user,
            ));
        }
    }

    private function bindUser(OauthUser $oauthUser, $password)
    {
        $user = $this->getUserByTypeAndAccount($oauthUser->accountType, $oauthUser->account);

        $isCorrectPassword = $this->getUserService()->verifyPassword($user['id'], $password);
        if ($isCorrectPassword) {
            $this->getUserService()->bindUser($oauthUser->type, $oauthUser->id, $user['id'], null);
            $this->authenticatedOauthUser();
            return true;
        } else {
            return false;
        }
    }

    private function authenticatedOauthUser()
    {
        $request = $this->get('request');
        $oauthUser = $this->getOauthUser($this->get('request'));
        $oauthUser->authenticated = true;
        $request->getSession()->set('oauth_user', $oauthUser);
    }

    public function successAction(Request $request)
    {
        $oauthUser = $this->getOauthUser($request);

        $user = $this->getUserByTypeAndAccount($oauthUser->accountType, $oauthUser->account);

        if (!$user || !$oauthUser->authenticated) {
            throw new NotFoundHttpException();
        }

        if ($oauthUser->isApp) {
            $token = $this->getUserService()->makeToken('mobile_login', $user['id'], time() + 3600 * 24 * 30);
        } else {
            $request->getSession()->set('oauth_user', null);
            $this->authenticateUser($user);
            $token = null;
        }

        return $this->render('oauth2/success.html.twig', array(
            'oauthUser' => $oauthUser,
            'token' => $token,
            'isCreate' => $request->query->get('isCreate'),
        ));
    }

    public function createAction(Request $request)
    {
        $oauthUser = $this->getOauthUser($request);

        return $this->render('oauth2/create-account.html.twig', array(
            'oauthUser' => $oauthUser,
        ));
    }

    private function getUserByTypeAndAccount($type, $account)
    {
        $user = null;
        switch ($type) {
            case 'email':
                $user = $this->getUserService()->getUserByEmail($account);
                break;
            case 'mobile':
                $user = $this->getUserService()->getUserByVerifiedMobile($account);
                break;
            default:
                throw new NotFoundHttpException();
        }

        return $user;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \AppBundle\Controller\OAuth2\OauthUser
     */
    private function getOauthUser(Request $request)
    {
        $oauthUser = $request->getSession()->get('oauth_user');
        if (!$oauthUser) {
            throw new NotFoundHttpException();
        }

        return $oauthUser;
    }

    private function loginAttemptCheck($account, Request $request)
    {
        $limiter = new LoginFailRateLimiter($this->getBiz());
        $request->request->set('username', $account);
        $limiter->handle($request);
    }
}
