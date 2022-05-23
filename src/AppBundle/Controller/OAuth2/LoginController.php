<?php

namespace AppBundle\Controller\OAuth2;

use AppBundle\Common\TimeMachine;
use AppBundle\Component\RateLimit\LoginFailRateLimiter;
use AppBundle\Component\RateLimit\RegisterRateLimiter;
use AppBundle\Controller\LoginBindController;
use Biz\Common\BizSms;
use Biz\Common\CommonException;
use Biz\Distributor\Util\DistributorCookieToolkit;
use Biz\User\UserException;
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

    public function tryBindAccountAction(Request $request)
    {
        $type = $request->request->get('accountType');
        $account = $request->request->get('account');

        $user = $this->getUserByTypeAndAccount($type, $account);
        if (empty($user)) {
            return $this->createJsonResponse(['success' => true]);
        }
        $oauthUser = $this->getOauthUser($request);
        $userBind = $this->getUserService()->getUserBindByTypeAndUserId($oauthUser->type, $user['id']);
        if ($userBind) {
            return $this->createJsonResponse(['success' => false, 'message' => $this->trans('user.oauth.bind_error.bind_by_other')]);
        }

        return $this->createJsonResponse(['success' => true]);
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
        $oauthUser = $this->getOauthUser($request);
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

            $bindMobile = $request->request->get('originalMobileAccount', '');
            $oauthUser->captchaEnabled = $bindMobile && OAuthUser::MOBILE_TYPE != $oauthUser->accountType ? false : $oauthUser->captchaEnabled;
            $this->registerAttemptCheck($request);

            if ($request->request->get('originalEmailAccount', '') && $request->request->get('originalAccountPassword', '')) {
                $this->bindOriginalEmailAccount($request);
            } else {
                $originMobileUser = $this->getUserService()->getUserByVerifiedMobile($bindMobile);
                if ($originMobileUser && $request->request->get('accountSmsCode')) {
                    $this->bindOriginalMobileAccount($request);
                } else {
                    $this->register($request);
                }
            }
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

    protected function bindOriginalEmailAccount(Request $request)
    {
        $oauthUser = $this->getOauthUser($request);
        $registerFields = $request->request->all();
        $originalEmailAccount = $request->request->get('originalEmailAccount');
        $originalAccountPassword = $request->request->get('originalAccountPassword');

        $user = $this->getUserService()->getUserByEmail($originalEmailAccount);
        if (!$user || !empty($user['verifiedMobile'])) {
            throw UserException::FORBIDDEN_REGISTER();
        }

        $validatePassed = $this->getAuthService()->checkPassword($user['id'], $originalAccountPassword);
        if (!$validatePassed) {
            throw UserException::EMAIL_PASSWORD_ERROR();
        } else {
            $this->loginAttemptCheck($oauthUser->account, $request);
            $token = $request->getSession()->get('oauth_token');
            $this->getUserService()->bindUser($oauthUser->type, $oauthUser->authid, $user['id'], $token);
            $registerFields['nickname'] && $this->getUserService()->changeNickname($user['id'], $registerFields['nickname']);
            $this->getUserService()->changeMobile($user['id'], $oauthUser->account);
            $this->getUserService()->initPassword($user['id'], $registerFields['password']);
        }
    }

    protected function bindOriginalMobileAccount(Request $request)
    {
        $oauthUser = $this->getOauthUser($request);
        $registerFields = $request->request->all();
        $originalMobileAccount = $request->request->get('originalMobileAccount');

        $user = $this->getUserService()->getUserByVerifiedMobile($originalMobileAccount);
        if (!$user || (!empty($user['email'] && $user['emailVerified']))) {
            throw UserException::FORBIDDEN_REGISTER();
        }

        $this->loginAttemptCheck($oauthUser->account, $request);
        $token = $request->getSession()->get('oauth_token');
        $this->getUserService()->bindUser($oauthUser->type, $oauthUser->authid, $user['id'], $token);
        $registerFields['nickname'] && $this->getUserService()->changeNickname($user['id'], $registerFields['nickname']);
        $this->getUserService()->changeEmail($user['id'], $oauthUser->account);
        $this->getUserService()->initPassword($user['id'], $registerFields['password']);
    }

    public function OriginalAccountCheckAction(Request $request, $type)
    {
        $account = $request->query->get('value');

        return 'mobile' == $type ? $this->checkMobile($account) : $this->checkEmail($account);
    }

    protected function checkMobile($mobile)
    {
        $bindMode = $this->getSettingService()->node('login_bind.mobile_bind_mode', 'constraint');

        if ('constraint' == $bindMode && empty($mobile)) {
            return $this->validateResult('false', '请输入手机号');
        }

        if ('constraint' != $bindMode && empty($mobile)) {
            return $this->validateResult('success', '');
        }

        $user = $this->getUserService()->getUserByVerifiedMobile($mobile);
        if ($user && !empty($user['email']) && $user['emailVerified']) {
            return $this->validateResult('false', '该手机账号已绑定邮箱');
        }

        return $this->validateResult('success', '');
    }

    protected function checkEmail($email)
    {
        $user = $this->getUserService()->getUserByEmail($email);
        if (empty($user)) {
            return $this->validateResult('false', '该邮箱帐号不存在');
        }

        if (!empty($user['verifiedMobile'])) {
            return $this->validateResult('false', '该邮箱帐号已绑定手机号');
        }

        return $this->validateResult('success', '');
    }

    protected function validateResult($result, $message)
    {
        $response = true;
        if ('success' !== $result) {
            $response = $message;
        }

        return $this->createJsonResponse($response);
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

        if ($request->request->get('originalMobileAccount') && $request->request->get('accountSmsCode')) {
            $smsToken = $request->request->get('smsToken');
            $mobile = $request->request->get('originalMobileAccount');
            $smsCode = $request->request->get('accountSmsCode');
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
            'registerVisitId' => $request->request->get('registerVisitId', ''), // 支持统计分析注册标识,比如:nuwa
        ];

        if (OAuthUser::MOBILE_TYPE == $oauthUser->accountType) {
            $registerFields['mobile'] = $registerFields['verifiedMobile'] = $oauthUser->account;
            $registerFields['email'] = $this->getUserService()->generateEmail($registerFields);
        }

        $bindMobile = $request->request->get('originalMobileAccount');
        if (OAuthUser::EMAIL_TYPE == $oauthUser->accountType && $bindMobile) {
            $registerFields['verifiedMobile'] = $bindMobile;
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
