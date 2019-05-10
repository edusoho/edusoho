<?php

namespace AppBundle\Controller;

use AppBundle\Common\Exception\AbstractException;
use Biz\Common\CommonException;
use Biz\System\Service\LogService;
use Biz\System\Service\SettingService;
use Biz\User\Service\AuthService;
use Biz\User\Service\MessageService;
use Biz\User\Service\NotificationService;
use Biz\User\Service\UserFieldService;
use Biz\Distributor\Util\DistributorCookieToolkit;
use AppBundle\Common\SmsToolkit;
use AppBundle\Common\SimpleValidator;
use Biz\User\UserException;
use Gregwar\Captcha\CaptchaBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RegisterController extends BaseController
{
    public function indexAction(Request $request)
    {
        $fields = $request->query->all();
        $user = $this->getCurrentUser();

        if ($user->isLogin()) {
            return $this->createMessageResponse('info', '你已经登录了', null, 3000, $this->getTargetPath($request));
        }

        $registerEnable = $this->getAuthService()->isRegisterEnabled();

        if (!$registerEnable) {
            return $this->createMessageResponse('info', '注册已关闭，请联系管理员', null, 3000, $this->getTargetPath($request));
        }

        if ('POST' === $request->getMethod()) {
            try {
                $registration = $request->request->all();
                unset($registration['type']);

                if (isset($registration['emailOrMobile']) && SimpleValidator::mobile($registration['emailOrMobile'])) {
                    $registration['verifiedMobile'] = $registration['emailOrMobile'];
                }

                $registration['mobile'] = isset($registration['verifiedMobile']) ? $registration['verifiedMobile'] : '';
                $registration['createdIp'] = $request->getClientIp();
                $authSettings = $this->getSettingService()->get('auth', array());

                //拖动校验
                $this->dragCaptchaValidator($registration, $authSettings);

                //手机校验码
                if ($this->smsCodeValidator($authSettings, $registration)) {
                    $registration['verifiedMobile'] = '';
                    $request->request->add(array_merge($request->request->all(), array('mobile' => $registration['mobile'])));

                    list($result, $sessionField, $requestField) = SmsToolkit::smsCheck($request, $scenario = 'sms_registration');

                    if ($result) {
                        $registration['verifiedMobile'] = $sessionField['to'];
                    } else {
                        return $this->createMessageResponse('info', '手机号码和短信验证码不匹配，请重新注册');
                    }
                }

                $registration['createdIp'] = $request->getClientIp();
                $registration['registeredWay'] = 'web';
                $registration = DistributorCookieToolkit::setCookieTokenToFields($request, $registration, DistributorCookieToolkit::USER);

                $user = $this->getAuthService()->register($registration);

                if (($authSettings
                        && isset($authSettings['email_enabled'])
                        && 'closed' === $authSettings['email_enabled'])
                    || !$this->isEmptyVeryfyMobile($user)
                ) {
                    $this->authenticateUser($user);
                }

                $goto = $this->generateUrl('register_submited', array(
                    'id' => $user['id'],
                    'hash' => $this->makeHash($user),
                    'goto' => $this->getTargetPath($request),
                ));

                if ($this->getAuthService()->hasPartnerAuth()) {
                    $currentUser = $this->getCurrentUser();

                    if (!$currentUser->isLogin()) {
                        $this->authenticateUser($user);
                    }

                    $goto = $this->generateUrl('partner_login', array('goto' => $goto));
                }

                $response = $this->redirect($this->generateUrl('register_success', array('goto' => $goto)));
                $response = DistributorCookieToolkit::clearCookieToken(
                    $request,
                    $response,
                    array('checkedType' => DistributorCookieToolkit::USER)
                );

                return $response;
            } catch (AbstractException $se) {
                $this->setFlashMessage('danger', $this->trans($se->getMessage()));
            } catch (\Exception $e) {
                return $this->createMessageResponse('error', $this->trans($e->getMessage()));
            }
        }

        // invitedCode这里为 被邀请码
        $invitedCode = $this->setInviteCode($request);
        $inviteUser = empty($invitedCode) ? array() : $this->getUserService()->getUserByInviteCode($invitedCode);

        if ($this->getWebExtension()->isWechatLoginBind()) {
            return $this->redirect($this->generateUrl('login_bind', array('type' => 'weixinmob', '_target_path' => $this->getTargetPath($request))));
        }

        return $this->render('register/index.html.twig', array(
            'isRegisterEnabled' => $registerEnable,
            'registerSort' => array(),
            'inviteUser' => $inviteUser,
            '_target_path' => $this->getTargetPath($request),
        ));
    }

    private function setInviteCode($request)
    {
        // invitedCode这里为 被邀请码
        $invitedCode = $request->query->get('inviteCode', '');
        $inviteSetting = $this->getSettingService()->get('invite');
        if (empty($inviteSetting['invite_code_setting'])) {
            $invitedCode = '';
        }
        $this->get('session')->set('invitedCode', $invitedCode);

        return $invitedCode;
    }

    public function successAction(Request $request)
    {
        $goto = $request->query->get('goto');

        if (empty($goto)) {
            $goto = $this->generateUrl('homepage');
        }

        return $this->createMessageResponse('info', '正在跳转页面，请稍等......', '注册成功', 1, $goto);
    }

    protected function isMobileRegister($registration)
    {
        if (isset($registration['emailOrMobile']) && !empty($registration['emailOrMobile'])) {
            if (SimpleValidator::mobile($registration['emailOrMobile'])) {
                return true;
            }
        } elseif (isset($registration['mobile']) && !empty($registration['mobile'])) {
            if (SimpleValidator::mobile($registration['mobile'])) {
                return true;
            }
        }

        return false;
    }

    protected function isEmptyVeryfyMobile($user)
    {
        if (isset($user['verifiedMobile']) && !empty($user['verifiedMobile'])) {
            return false;
        }

        return true;
    }

    public function userTermsAction(Request $request)
    {
        $setting = $this->getSettingService()->get('auth', array());

        return $this->render('register/user-terms.html.twig', array(
            'userTerms' => $setting['user_terms_body'],
        ));
    }

    public function emailSendAction(Request $request, $id, $hash)
    {
        $user = $this->checkHash($id, $hash);

        if (empty($user)) {
            return $this->createJsonResponse(false);
        }

        $token = $this->getUserService()->makeToken('email-verify', $user['id'], strtotime('+1 day'));
        $this->sendVerifyEmail($token, $user);

        return $this->createJsonResponse(true);
    }

    public function submitedAction(Request $request, $id, $hash)
    {
        $user = $this->checkHash($id, $hash);

        if (empty($user)) {
            $this->createNewException(UserException::NOTFOUND_USER());
        }

        $auth = $this->getSettingService()->get('auth');

        if (!empty($user['verifiedMobile'])) {
            return $this->redirect($this->getTargetPath($request));
        }

        if ($auth && 'mobile' !== $auth['register_mode']
            && array_key_exists('email_enabled', $auth)
            && ('opened' === $auth['email_enabled'])
        ) {
            return $this->render('register/email-verify.html.twig', array(
                'user' => $user,
                'hash' => $hash,
                'emailLoginUrl' => $this->getEmailLoginUrl($user['email']),
                '_target_path' => $this->getTargetPath($request),
            ));
        }
        $this->authenticateUser($user);

        return $this->redirect($this->getTargetPath($request));
    }

    public function emailVerifyAction(Request $request, $token)
    {
        $token = $this->getUserService()->getToken('email-verify', $token);

        if (empty($token)) {
            $currentUser = $this->getCurrentUser();

            if (empty($currentUser) || 0 == $currentUser['id']) {
                return $this->render('register/email-verify-error.html.twig');
            }

            return $this->redirect($this->generateUrl('settings'));
        }

        $user = $this->getUserService()->getUser($token['userId']);

        if (empty($user)) {
            return $this->createNotFoundException();
        }

        $this->authenticateUser($user);
        $this->getUserService()->setEmailVerified($user['id']);

        if ('POST' === strtoupper($request->getMethod())) {
            $this->getUserService()->deleteToken('email-verify', $token['token']);

            return $this->createJsonResponse(true);
        }

        return $this->render('register/email-verify-success.html.twig', array(
            'token' => $token,
        ));
    }

    public function resetEmailAction(Request $request, $id, $hash)
    {
        $user = $this->checkHash($id, $hash);

        if ($request->isMethod('post')) {
            $password = $request->request->get('password');
            $email = $request->request->get('email');

            if ($user['email'] !== $email) {
                $this->createNewException(UserException::NOT_MATCH_BIND_EMAIL());
            }

            $user = $this->getUserService()->getUserByEmail($email);

            if (!$this->getUserService()->verifyPassword($user['id'], $password)) {
                $this->setFlashMessage('danger', 'site.incorrect.password');
            } else {
                $token = $this->getUserService()->makeToken('email-reset', $user['id'], strtotime('+10 minutes'), array(
                    'password' => $password,
                ));

                return $this->render('register/reset-email-step2.html.twig', array(
                    'token' => $token,
                ));
            }
        }

        if (empty($user)) {
            $this->createNewException(UserException::NOTFOUND_USER());
        }

        return $this->render('register/reset-email-step1.html.twig', array(
            'id' => $id,
            'hash' => $hash,
            'user' => $user,
        ));
    }

    public function resetEmailVerifyAction(Request $request)
    {
        $newEmail = $request->request->get('email');

        if (empty($newEmail)) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $token = $request->request->get('token');
        $token = $this->getUserService()->getToken('email-reset', $token);
        if (empty($token)) {
            return $this->createNotFoundException('token已失效');
        }

        $user = $this->getUserService()->getUser($token['userId']);

        if (empty($user)) {
            $this->createNewException(UserException::NOTFOUND_USER());
        }

        $authSettings = $this->getSettingService()->get('auth', array());
        $dragCaptchaToken = $request->request->get('dragCaptchaToken');

        //拖动校验
        $this->dragCaptchaValidator(array('dragCaptchaToken' => $dragCaptchaToken), $authSettings);

        $this->getAuthService()->changeEmail($user['id'], $token['data']['password'], $newEmail);
        $user = $this->getUserService()->getUser($user['id']);

        return $this->redirect($this->generateUrl('register_submited', array(
            'id' => $user['id'],
            'hash' => $this->makeHash($user),
            'goto' => $this->generateUrl('homepage'),
        )));
    }

    public function resetEmailCheckAction(Request $request)
    {
        $email = $request->query->get('value');
        $email = str_replace('!', '.', $email);
        $user = $this->getUserService()->getUserByEmail($email);

        if (empty($user)) {
            $response = array('success' => false, 'message' => '该Email不存在');
        } else {
            $response = array('success' => true, 'message' => '');
        }

        return $this->createJsonResponse($response);
    }

    protected function makeHash($user)
    {
        $string = $user['id'].$user['email'].$this->container->getParameter('secret');

        return md5($string);
    }

    protected function checkHash($userId, $hash)
    {
        $user = $this->getUserService()->getUser($userId);

        if (empty($user)) {
            return false;
        }

        if ($this->makeHash($user) !== $hash) {
            return false;
        }

        return $user;
    }

    public function emailCheckAction(Request $request)
    {
        $email = $request->query->get('value');
        $email = str_replace('!', '.', $email);
        list($result, $message) = $this->getAuthService()->checkEmail($email);

        return $this->validateResult($result, $message);
    }

    public function mobileCheckAction(Request $request)
    {
        $mobile = $request->query->get('value');
        list($result, $message) = $this->getAuthService()->checkMobile($mobile);

        return $this->validateResult($result, $message);
    }

    public function emailOrMobileCheckAction(Request $request)
    {
        $emailOrMobile = $request->query->get('value');
        $emailOrMobile = str_replace('!', '.', $emailOrMobile);
        list($result, $message) = $this->getAuthService()->checkEmailOrMobile($emailOrMobile);

        return $this->validateResult($result, $message);
    }

    protected function validateResult($result, $message)
    {
        $response = true;
        if ('success' !== $result) {
            $response = $message;
        }

        return $this->createJsonResponse($response);
    }

    public function nicknameCheckAction(Request $request)
    {
        $nickname = $request->query->get('value');
        $randomName = $request->query->get('randomName');
        list($result, $message) = $this->getAuthService()->checkUsername($nickname, $randomName);

        return $this->validateResult($result, $message);
    }

    public function invitecodeCheckAction(Request $request)
    {
        $inviteCode = $request->query->get('value');
        $user = $this->getUserService()->getUserByInviteCode($inviteCode);

        if (empty($user)) {
            return $this->validateResult('false', '邀请码不正确');
        }

        return $this->validateResult('success', '');
    }

    public function captchaModalAction()
    {
        return $this->render('register/captcha-modal.html.twig', array());
    }

    public function captchaCheckAction(Request $request)
    {
        $captchaFilledByUser = strtolower($request->query->get('value'));

        if ($request->getSession()->get('captcha_code') == $captchaFilledByUser) {
            $response = array('success' => true, 'message' => '验证码正确');
        } else {
            $request->getSession()->set('captcha_code', mt_rand(0, 999999999));
            $response = array('success' => false, 'message' => '验证码错误');
        }

        return $this->createJsonResponse($response);
    }

    public function getEmailLoginUrl($email)
    {
        $host = substr($email, strpos($email, '@') + 1);

        if ('hotmail.com' === $host) {
            return 'http://www.'.$host;
        }

        if ('gmail.com' === $host) {
            return 'http://mail.google.com';
        }

        return 'http://mail.'.$host;
    }

    public function analysisAction(Request $request)
    {
        return $this->render('register/analysis.html.twig', array());
    }

    public function captchaAction(Request $request)
    {
        $imgBuilder = new CaptchaBuilder();
        $imgBuilder->build($width = 150, $height = 32, $font = null);
        $request->getSession()->set('captcha_code', strtolower($imgBuilder->getPhrase()));

        ob_start();
        $imgBuilder->output();
        $str = ob_get_clean();
        $imgBuilder = null;

        $headers = array(
            'Content-type' => 'image/jpeg',
            'Content-Disposition' => 'inline; filename="'.'reg_captcha.jpg'.'"', );

        return new Response($str, 200, $headers);
    }

    protected function sendRegisterMessage($user)
    {
        $senderUser = array();
        $auth = $this->getSettingService()->get('auth', array());

        if (empty($auth['welcome_enabled'])) {
            return false;
        }

        if ('opened' !== $auth['welcome_enabled']) {
            return false;
        }

        if (empty($auth['welcome_sender'])) {
            return false;
        }

        $senderUser = $this->getUserService()->getUserByNickname($auth['welcome_sender']);

        if (empty($senderUser)) {
            return false;
        }

        $welcomeBody = $this->getWelcomeBody($user);

        if (empty($welcomeBody)) {
            return true;
        }

        if (strlen($welcomeBody) >= 1000) {
            $welcomeBody = $this->getWebExtension()->plainTextFilter($welcomeBody, 1000);
        }

        $this->getMessageService()->sendMessage($senderUser['id'], $user['id'], $welcomeBody);
        $conversation = $this->getMessageService()->getConversationByFromIdAndToId($user['id'], $senderUser['id']);
        $this->getMessageService()->deleteConversation($conversation['id']);
    }

    protected function getWelcomeBody($user)
    {
        $site = $this->getSettingService()->get('site', array());
        $valuesToBeReplace = array('{{nickname}}', '{{sitename}}', '{{siteurl}}');
        $valuesToReplace = array($user['nickname'], $site['name'], $site['url']);
        $welcomeBody = $this->setting('auth.welcome_body', '注册欢迎的内容');

        return str_replace($valuesToBeReplace, $valuesToReplace, $welcomeBody);
    }

    protected function sendVerifyEmail($token, $user)
    {
        try {
            $site = $this->getSettingService()->get('site', array());
            $verifyurl = $this->generateUrl('register_email_verify', array('token' => $token), true);
            $mailOptions = array(
                'to' => $user['email'],
                'template' => 'email_registration',
                'params' => array(
                    'sitename' => $site['name'],
                    'siteurl' => $site['url'],
                    'verifyurl' => $verifyurl,
                    'nickname' => $user['nickname'],
                ),
            );
            $mailFactory = $this->getBiz()->offsetGet('mail_factory');
            $mail = $mailFactory($mailOptions);
            $mail->send();
        } catch (\Exception $e) {
            $this->getLogService()->error('user', 'register', '注册激活邮件发送失败:'.$e->getMessage());
        }
    }

    protected function getWebExtension()
    {
        return $this->container->get('web.twig.extension');
    }

    protected function dragCaptchaValidator($registration, $authSettings)
    {
        if (array_key_exists('captcha_enabled', $authSettings) && (1 == $authSettings['captcha_enabled']) && empty($registration['mobile'])) {
            $biz = $this->getBiz();
            $bizDragCaptcha = $biz['biz_drag_captcha'];

            $dragcaptchaToken = empty($registration['dragCaptchaToken']) ? '' : $registration['dragCaptchaToken'];
            $bizDragCaptcha->check($dragcaptchaToken);
        }
    }

    //validate captcha
    protected function captchaEnabledValidator($authSettings, $registration, Request $request)
    {
        if (array_key_exists('captcha_enabled', $authSettings) && (1 == $authSettings['captcha_enabled']) && !isset($registration['mobile'])) {
            $captchaCodePostedByUser = strtolower($registration['captcha_code']);
            $captchaCode = $request->getSession()->get('captcha_code');

            if (!isset($captchaCodePostedByUser) || strlen($captchaCodePostedByUser) < 5) {
                $this->createNewException(CommonException::FORBIDDEN_DRAG_CAPTCHA_ERROR());
            }

            if (!isset($captchaCode) || strlen($captchaCode) < 5) {
                $this->createNewException(CommonException::FORBIDDEN_DRAG_CAPTCHA_ERROR());
            }

            if ($captchaCode != $captchaCodePostedByUser) {
                $request->getSession()->set('captcha_code', mt_rand(0, 999999999));
                $this->createNewException(CommonException::FORBIDDEN_DRAG_CAPTCHA_ERROR());
            }

            $request->getSession()->set('captcha_code', mt_rand(0, 999999999));
        }
    }

    protected function smsCodeValidator($authSettings, $registration)
    {
        if (
            in_array($authSettings['register_mode'], array('mobile', 'email_or_mobile'))
            && isset($registration['mobile']) && !empty($registration['mobile'])
            && '1' == $this->setting('cloud_sms.sms_enabled')
        ) {
            return true;
        }
    }

    /**
     * @return UserFieldService
     */
    protected function getUserFieldService()
    {
        return $this->getBiz()->service('User:UserFieldService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }

    /**
     * @return MessageService
     */
    protected function getMessageService()
    {
        return $this->getBiz()->service('User:MessageService');
    }

    /**
     * @return NotificationService
     */
    protected function getNotificationService()
    {
        return $this->getBiz()->service('User:NotificationService');
    }

    /**
     * @return AuthService
     */
    protected function getAuthService()
    {
        return $this->getBiz()->service('User:AuthService');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->getBiz()->service('System:LogService');
    }

    /**
     * @return DistributorService
     */
    protected function getDistributorService()
    {
        return $this->getBiz()->service('Distributor:DistributorService');
    }
}
