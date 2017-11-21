<?php

namespace AppBundle\Controller\OAuth2;

use AppBundle\Common\RegisterTypeUtils;
use AppBundle\Common\TimeMachine;
use AppBundle\Component\RateLimit\LoginFailRateLimiter;
use AppBundle\Component\RateLimit\RegisterRateLimiter;
use AppBundle\Controller\LoginBindController;
use Biz\Common\BizSms;
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
        $os = $request->query->get('os');

        if (!in_array($os, array('iOS', 'Android'))) {
            throw $this->createNotFoundException();
        }

        $client = $this->createOAuthClient($type);
        $oUser = $client->getUserInfo($this->makeFakeToken($type, $accessToken, $openid));

        $this->storeOauthUserToSession($request, $oUser, $type, $os);

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
        $oauthUser->captchaEnabled = $oauthUser->accountType == OAuthUser::MOBILE_TYPE || $oauthUser->captchaEnabled;

        if ($user) {
            $redirectUrl = $this->generateUrl('oauth2_login_bind_login');
        } else {
            $redirectUrl = $this->generateUrl('oauth2_login_create');
        }

        $request->getSession()->set(OAuthUser::SESSION_KEY, $oauthUser);

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

    private function bindUser(OAuthUser $oauthUser, $password)
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
        $request->getSession()->set(OAuthUser::SESSION_KEY, $oauthUser);
    }

    public function successAction(Request $request)
    {
        $oauthUser = $this->getOauthUser($request);

        $user = $this->getUserByTypeAndAccount($oauthUser->accountType, $oauthUser->account);

        if (!$user || !$oauthUser->authenticated) {
            throw new NotFoundHttpException();
        }
        if ($oauthUser->isApp()) {
            $token = $this->getUserService()->makeToken('mobile_login', $user['id'], time() + TimeMachine::ONE_MONTH);
        } else {
            $request->getSession()->set(OAuthUser::SESSION_KEY, null);
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

        if ('POST' == $request->getMethod()) {
            $validateResult = $this->validateRegisterRequest($request);

            if ($validateResult['hasError']) {
                return $this->createFailJsonResponse(array('msg' => $validateResult['msg']));
            }

            $this->registerAttemptCheck($request);
            $this->register($request);
            $this->authenticatedOauthUser();

            return $this->createSuccessJsonResponse(array('url' => $this->generateUrl('oauth2_login_success', array('isCreate' => 1))));
        } else {
            return $this->render('oauth2/create-account.html.twig', array(
                'oauthUser' => $oauthUser,
            ));
        }
    }

    private function validateRegisterRequest(Request $request)
    {
        $validateResult = array(
            'hasError' => false,
        );

        $oauthUser = $this->getOauthUser($request);
        if ($oauthUser->accountType == OAuthUser::MOBILE_TYPE) {
            $smsToken = $request->request->get('smsToken');
            $mobile = $request->request->get(OAuthUser::MOBILE_TYPE);
            $smsCode = $request->request->get('smsCode');
            $status = $this->getBizSms()->check(BizSms::SMS_BIND_TYPE, $mobile, $smsToken, $smsCode);

            $validateResult['hasError'] = BizSms::STATUS_SUCCESS !== $status;
            $validateResult['msg'] = $status;
        }

        return $validateResult;
    }

    private function register(Request $request)
    {
        $oauthUser = $this->getOauthUser($request);
        $registerFields = array(
            'nickname' => $request->request->get('nickname'),
            $oauthUser->accountType => $oauthUser->account,
            'password' => $request->request->get('password'),
        );

        $this->getUserService()->register($registerFields, $oauthUser->type, RegisterTypeUtils::getRegisterTypes($registerFields, $oauthUser->type));
    }

    /**
     * @return \Biz\Common\BizSms
     */
    private function getBizSms()
    {
        $biz = $this->getBiz();

        return $biz['biz_sms'];
    }

    private function getUserByTypeAndAccount($type, $account)
    {
        $user = null;
        switch ($type) {
            case OAuthUser::EMAIL_TYPE:
                $user = $this->getUserService()->getUserByEmail($account);
                break;
            case OAuthUser::MOBILE_TYPE:
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
     * @return \AppBundle\Controller\OAuth2\OAuthUser
     */
    private function getOauthUser(Request $request)
    {
        $oauthUser = $request->getSession()->get(OAuthUser::SESSION_KEY);
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

    private function registerAttemptCheck(Request $request)
    {
        $limiter = new RegisterRateLimiter($this->getBiz());
        $limiter->handle($request);
    }
}
