<?php

namespace AppBundle\Controller\OAuth2;

use ApiBundle\Api\Resource\Setting\Setting;
use AppBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LoginController extends BaseController
{
    public function indexAction(Request $request)
    {
        $setting = new Setting($this->container, $this->getBiz());

        $registerSetting = $setting->getRegister();

        $oauthUser = $this->getOauthUser($request);

        return $this->render('oauth2/index.html.twig', array(
            'mode' => $registerSetting['mode'],
            'oauthUser' => $oauthUser,
        ));
    }

    public function bindAccountAction(Request $request)
    {
        $oauthUser = $this->getOauthUser($request);

        $type = $request->request->get('accountType');
        $account = $request->request->get('account');

        $user = $this->getUserByTypeAndAccount($type, $account);
        $oauthUser['accountType'] = $type;
        $oauthUser['account'] = $account;

        if ($user) {
            $oauthUser['esUserId'] = $user['id'];
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

            $isCorrectPW = $this->getUserService()->verifyPassword($oauthUser['esUserId'], $password);

            return $this->redirect($this->getTargetPath($request));
        } else {
            return $this->render('oauth2/bind-login.html.twig', array(
                'oauthUser' => $oauthUser,
            ));
        }
    }

    public function successAction(Request $request)
    {
        $oauthUser = $this->getOauthUser($request);

        return $this->render('wap/third-party/third-party-login-success.html.twig', array(
            'oauthUser' => $oauthUser,
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

    private function getOauthUser(Request $request)
    {
        $oauthUser = $request->getSession()->get('oauth_user');
        if (!$oauthUser) {
            throw new NotFoundHttpException();
        }

        return $oauthUser;
    }

    private function getIPRateLimiter()
    {
    }
}
