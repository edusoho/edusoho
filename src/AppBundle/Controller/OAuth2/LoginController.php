<?php

namespace AppBundle\Controller\OAuth2;

use AppBundle\Common\TimeMachine;
use AppBundle\Component\RateLimit\LoginFailRateLimiter;
use AppBundle\Component\RateLimit\RegisterRateLimiter;
use AppBundle\Controller\LoginBindController;
use Biz\Common\BizSms;
use Biz\Common\CommonException;
use Biz\Distributor\Util\DistributorCookieToolkit;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LoginController extends LoginBindController
{
    public function mainAction(Request $request)
    {
        $oauthUser = $this->getOauthUser($request);

        return $this->render('oauth2/index.html.twig', [
            'oauthUser' => $oauthUser,
        ]);
    }

    public function appAction(Request $request)
    {
        $accessToken = $request->query->get('access_token');
        $openid = $request->query->get('openid');
        $type = $request->query->get('type');
        $os = $request->query->get('os');
        $appid = $request->query->get('appid');

        if (!in_array($os, ['iOS', 'Android'])) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }

        $client = $this->createOAuthClient($type);
        $oUser = $client->getUserInfo($client->makeToken($type, $accessToken, $openid, $appid));

        $this->storeOauthUserToSession($request, $oUser, $type, $os);

        return $this->redirect($this->generateUrl('oauth2_login_index'));
    }

    public function bindAccountAction(Request $request)
    {
        $oauthUser = $this->getOauthUser($request);

        $type = $request->request->get('accountType');
        $account = $request->request->get('account');

        $user = $this->getUserByTypeAndAccount($type, $account);
        $oauthUser->accountType = $type;
        $oauthUser->account = $account;
        $oauthUser->isNewAccount = $user ? false : true;
        $oauthUser->captchaEnabled = OAuthUser::MOBILE_TYPE == $oauthUser->accountType ? false : true;

        if ($oauthUser->isNewAccount) {
            $redirectUrl = $this->generateUrl('oauth2_login_create');
        } else {
            $redirectUrl = $this->generateUrl('oauth2_login_bind_login');
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
            $token = $request->getSession()->get('oauth_token');

            $isSuccess = $this->bindUser($oauthUser, $password, $token);

            return $isSuccess ?
                $this->createSuccessJsonResponse(['url' => $this->generateUrl('oauth2_login_success')]) :
                $this->createFailJsonResponse(['message' => $this->trans('user.settings.security.password_modify.incorrect_password')]);
        } else {
            $user = $this->getUserByTypeAndAccount($oauthUser->accountType, $oauthUser->account);

            return $this->render('oauth2/bind-login.html.twig', [
                'oauthUser' => $oauthUser,
                'esUser' => $user,
            ]);
        }
    }

    protected function bindUser(OAuthUser $oauthUser, $password, $token)
    {
        $user = $this->getUserByTypeAndAccount($oauthUser->accountType, $oauthUser->account);

        $isCorrectPassword = $this->getUserService()->verifyPassword($user['id'], $password);
        if ($isCorrectPassword) {
            $this->getUserService()->bindUser($oauthUser->type, $oauthUser->authid, $user['id'], $token);
            $this->authenticatedOauthUser();

            return true;
        } else {
            return false;
        }
    }

    protected function authenticatedOauthUser()
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
            $request->getSession()->set(OAuthUser::SESSION_SKIP_KEY, true);
            $token = $this->getUserService()->makeToken('mobile_login', $user['id'], time() + TimeMachine::ONE_MONTH);
            if ('h5' == $oauthUser->os) {
                $this->authenticateUser($user);
            }
        } else {
            $token = null;
            $this->authenticateUser($user);
        }

        $isNewAccount = $oauthUser->isNewAccount;
        if ($isNewAccount && !empty($oauthUser->avatar)) {
            $this->getUserService()->changeAvatarFromImgUrl($user['id'], $oauthUser->avatar);
        }

        $request->getSession()->set(OAuthUser::SESSION_KEY, null);

        return $this->render('oauth2/success.html.twig', [
            'oauthUser' => $oauthUser,
            'token' => $token,
            'isNewAccount' => $isNewAccount,
        ]);
    }

    public function createAction(Request $request)
    {
        $oauthUser = $this->getOauthUser($request);

        if ('POST' == $request->getMethod()) {
            $validateResult = $this->validateRegisterRequest($request);

            if ($validateResult['hasError']) {
                return $this->createFailJsonResponse(['msg' => $validateResult['msg']]);
            }

            $this->registerAttemptCheck($request);
            $this->register($request);
            $this->authenticatedOauthUser();

            $response = $this->createSuccessJsonResponse(['url' => $this->generateUrl('oauth2_login_success')]);
            $response = DistributorCookieToolkit::clearCookieToken(
                $request,
                $response,
                ['checkedType' => DistributorCookieToolkit::USER]
            );

            return $response;
        } else {
            $request->getSession()->set(OAuthUser::SESSION_KEY, $oauthUser);
            $invitedCode = $this->get('session')->get('invitedCode');
            $inviteUser = empty($invitedCode) ? [] : $this->getUserService()->getUserByInviteCode($invitedCode);

            return $this->render('oauth2/create-account.html.twig', [
                'oauthUser' => $oauthUser,
                'inviteUser' => $inviteUser,
                'captchaStatus' => $this->getUserService()->getSmsRegisterCaptchaStatus($request->getClientIp()),
            ]);
        }
    }

    protected function validateRegisterRequest(Request $request)
    {
        $validateResult = [
            'hasError' => false,
        ];

        $this->validateRegisterType($request);

        $oauthUser = $this->getOauthUser($request);
        if (OAuthUser::MOBILE_TYPE == $oauthUser->accountType) {
            $smsToken = $request->request->get('smsToken');
            $mobile = $request->request->get(OAuthUser::MOBILE_TYPE);
            $smsCode = $request->request->get('smsCode');
            $status = $this->getBizSms()->check(BizSms::SMS_BIND_TYPE, $mobile, $smsToken, $smsCode);

            $validateResult['hasError'] = BizSms::STATUS_SUCCESS !== $status;
            $validateResult['msg'] = $status;
        }

        return $validateResult;
    }

    protected function validateRegisterType(Request $request)
    {
        $oauthUser = $this->getOauthUser($request);
        $isCloseRegister = OAuthUser::REGISTER_CLOSED === $oauthUser->mode;
        $notEmailAccount = OAuthUser::EMAIL_TYPE === $oauthUser->mode && OAuthUser::EMAIL_TYPE !== $oauthUser->accountType;
        $notMobileAccount = OAuthUser::MOBILE_TYPE === $oauthUser->mode && OAuthUser::MOBILE_TYPE !== $oauthUser->accountType;
        if ($isCloseRegister || $notEmailAccount || $notMobileAccount) {
            throw new NotFoundHttpException();
        }
    }

    protected function register(Request $request)
    {
        $oauthUser = $this->getOauthUser($request);
        $registerFields = [
            'nickname' => $request->request->get('nickname'),
            'password' => $request->request->get('password'),
            'invitedCode' => $request->request->get('invitedCode'),
            $oauthUser->accountType => $oauthUser->account,
            'avatar' => $oauthUser->avatar,
            'type' => $oauthUser->type,
            'registeredWay' => $oauthUser->isApp() ? strtolower($oauthUser->os) : 'web',
            'authid' => $oauthUser->authid,
            'createdIp' => $request->getClientIp(),
        ];

        if (OAuthUser::MOBILE_TYPE == $oauthUser->accountType) {
            $registerFields['verifiedMobile'] = $oauthUser->account;
            $registerFields['email'] = $this->getUserService()->generateEmail($registerFields);
        }

        $registerFields = DistributorCookieToolkit::setCookieTokenToFields($request, $registerFields, DistributorCookieToolkit::USER);

        $this->getUserService()->register(
            $registerFields,
            $this->getRegisterTypeToolkit()->getThirdPartyRegisterTypes($oauthUser->accountType, $registerFields)
        );
    }

    /**
     * @return \Biz\Common\BizSms
     */
    protected function getBizSms()
    {
        $biz = $this->getBiz();

        return $biz['biz_sms'];
    }

    protected function getRegisterTypeToolkit()
    {
        $biz = $this->getBiz();

        return $biz['user.register.type.toolkit'];
    }

    protected function getUserByTypeAndAccount($type, $account)
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
     * @return \AppBundle\Controller\OAuth2\OAuthUser
     */
    protected function getOauthUser(Request $request)
    {
        $oauthUser = $request->getSession()->get(OAuthUser::SESSION_KEY);
        if (!$oauthUser) {
            throw new NotFoundHttpException();
        }

        return $oauthUser;
    }

    protected function loginAttemptCheck($account, Request $request)
    {
        $limiter = new LoginFailRateLimiter($this->getBiz());
        $request->request->set('username', $account);
        $limiter->handle($request);
    }

    protected function registerAttemptCheck(Request $request)
    {
        $limiter = new RegisterRateLimiter($this->getBiz());
        $limiter->handle($request);
    }
}
