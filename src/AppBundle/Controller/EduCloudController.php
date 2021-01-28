<?php

namespace AppBundle\Controller;

use AppBundle\Common\SmsToolkit;
use Biz\Sms\SmsException;
use Biz\System\SettingException;
use Biz\User\CurrentUser;
use Biz\User\Service\UserService;
use Biz\System\Service\LogService;
use Biz\CloudPlatform\CloudAPIFactory;
use Biz\System\Service\SettingService;
use Biz\Sms\SmsProcessor\SmsProcessorFactory;
use Biz\User\UserException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;

class EduCloudController extends BaseController
{
    public function smsSendAction(Request $request)
    {
        $smsType = $request->request->get('sms_type');

        return $this->createJsonResponse($this->sendSms($request, $smsType));
    }

    public function smsSendRegistrationAction(Request $request)
    {
        $smsType = 'sms_registration';

        $status = $this->getUserService()->getSmsRegisterCaptchaStatus($request->getClientIp());
        if ('captchaRequired' == $status) {
            $captchaNum = $request->request->get('captcha_num');
            if (empty($captchaNum)) {
                return $this->createJsonResponse(array('ACK' => 'captchaRequired'));
            } elseif (!$this->validateDragCaptcha($request)) {
                return $this->createJsonResponse(array('error' => '验证码错误'));
            }
        } elseif ('smsUnsendable' == $status) {
            return;
        }

        $result = $this->sendSms($request, $smsType);

        if (!empty($result['ACK']) && 'ok' == $result['ACK']) {
            $this->getUserService()->updateSmsRegisterCaptchaStatus($request->getClientIp());
        }

        return $this->createJsonResponse($result);
    }

    public function smsSendCheckCaptchaAction(Request $request)
    {
        $smsType = $request->request->get('sms_type');

        $status = $this->getUserService()->getSmsCommonCaptchaStatus($request->getClientIp());
        if ('captchaRequired' == $status) {
            $captchaNum = $request->request->get('captcha_num');
            if (empty($captchaNum)) {
                return $this->createJsonResponse(array('ACK' => 'captchaRequired'));
            } elseif (!$this->validateDragCaptcha($request)) {
                return $this->createJsonResponse(array('error' => '验证码错误'));
            }
        }

        $result = $this->sendSms($request, $smsType);

        if (!empty($result['ACK']) && 'ok' == $result['ACK']) {
            $this->getUserService()->getSmsCommonCaptchaStatus($request->getClientIp(), true);
        }

        return $this->createJsonResponse($result);
    }

    public function smsCheckAction(Request $request, $type)
    {
        $targetSession = $request->getSession()->get($type);
        $targetMobile = $targetSession['to'] ? $targetSession['to'] : '';
        $postSmsCode = $request->query->get('value', '');

        $ratelimiterResult = SmsToolkit::smsCheckRatelimiter($request, $type, $postSmsCode);
        if ($ratelimiterResult && false === $ratelimiterResult['success']) {
            return $this->createJsonResponse($ratelimiterResult);
        }

        if ('' === (string) $postSmsCode || '' === (string) $targetSession['sms_code']) {
            $response = array('success' => false, 'message' => 'json_response.verification_code_error.message');
        }

        $mobile = $request->query->get('mobile', '');

        if ('' != $mobile && !empty($targetSession['to']) && $mobile != $targetSession['to']) {
            return $this->createJsonResponse(array('success' => false, 'message' => 'json_response.verification_code_not_match.message'));
        }

        $response = array(
            'success' => false,
            'message' => 'json_response.verification_code_error.message',
        );

        if ($targetSession['sms_code'] == $request->query->get('value')) {
            $response['success'] = true;
            $response['message'] = 'json_response.verification_code_correct.message';
        }

        return $this->createJsonResponse($response);
    }

    public function cloudCallBackAction(Request $request)
    {
        $settings = $this->getSettingService()->get('storage', array());

        $data = $request->request->all();
        $webAccessKey = empty($settings['cloud_access_key']) ? '' : $settings['cloud_access_key'];

        if (!empty($data['accessKey']) && $data['accessKey'] == $webAccessKey && !empty($data['action'])) {
            $setting['message'] = empty($data['reason']) ? '' : $data['reason'];

            $setting['status'] = $data['action'];

            $this->getSettingService()->set('cloud_sms', $setting);

            return $this->createJsonResponse(array('status' => 'ok'));
        }

        return $this->createJsonResponse(array('error' => 'accessKey error!'));
    }

    public function smsCallBackAction(Request $request, $targetType, $targetId)
    {
        $index = $request->query->get('index');
        $smsType = $request->query->get('smsType');
        $originSign = rawurldecode($request->query->get('sign'));

        $url = $this->setting('site.url', '');
        $url = empty($url) ? $url : rtrim($url, ' \/');
        $url = empty($url) ? $this->generateUrl('edu_cloud_sms_send_callback', array('targetType' => $targetType, 'targetId' => $targetId), UrlGeneratorInterface::ABSOLUTE_URL) : $url.$this->generateUrl('edu_cloud_sms_send_callback', array('targetType' => $targetType, 'targetId' => $targetId));
        $url .= '?index='.$index.'&smsType='.$smsType;
        $api = CloudAPIFactory::create('leaf');
        $sign = $this->getSignEncoder()->encodePassword($url, $api->getAccessKey());

        if ($originSign != $sign) {
            return $this->createJsonResponse(array('error' => 'sign error'));
        }

        $processor = SmsProcessorFactory::create($targetType);

        $smsInfo = $processor->getSmsInfo($targetId, $index, $smsType);
        $this->getLogService()->info('sms', 'sms-callback', 'url: '.$url, $smsInfo);

        return $this->createJsonResponse($smsInfo);
    }

    public function searchCallBackAction(Request $request)
    {
        $originSign = rawurldecode($request->query->get('sign'));

        $searchSetting = $this->getSettingService()->get('cloud_search');

        $siteSetting = $this->getSettingService()->get('site');
        $siteSetting['url'] = rtrim($siteSetting['url']);
        $siteSetting['url'] = rtrim($siteSetting['url'], '/');

        $url = $siteSetting['url'];
        $url .= $this->generateUrl('edu_cloud_search_callback');
        $api = CloudAPIFactory::create('root');
        $sign = $this->getSignEncoder()->encodePassword($url, $api->getAccessKey());

        if ($originSign != $sign) {
            return $this->createJsonResponse(array('error' => 'sign不正确'));
        }

        $searchSetting['search_enabled'] = 1;
        $searchSetting['status'] = 'ok';

        $this->getSettingService()->set('cloud_search', $searchSetting);

        return $this->createJsonResponse(true);
    }

    protected function generateSmsCode($length = 6)
    {
        $code = rand(0, 9);

        for ($i = 1; $i < $length; ++$i) {
            $code = $code.rand(0, 9);
        }

        return $code;
    }

    protected function checkPhoneNum($num)
    {
        return preg_match("/^1\d{10}$/", $num);
    }

    protected function checkLastTime($smsLastTime, $currentTime, $allowedTime = 120)
    {
        if (!((0 == strlen($smsLastTime)) || (($currentTime - $smsLastTime) > $allowedTime))) {
            return false;
        }

        return true;
    }

    protected function checkSmsType($smsType, CurrentUser $user)
    {
        if (!in_array($smsType, array('sms_bind', 'sms_user_pay', 'sms_registration', 'sms_forget_password', 'sms_forget_pay_password', 'system_remind'))) {
            $this->createNewException(SmsException::ERROR_SMS_TYPE());
        }

        if ((!$user->isLogin()) && (in_array($smsType, array('sms_bind', 'sms_user_pay', 'sms_forget_pay_password')))) {
            $this->createNewException(UserException::UN_LOGIN());
        }

        if ('on' != $this->setting("cloud_sms.{$smsType}") && !$this->getUserService()->isMobileRegisterMode()) {
            $this->createNewException(SettingException::FORBIDDEN_MOBILE_REGISTER());
        }
    }

    protected function generateDescription($smsType)
    {
        $description = '';
        switch ($smsType) {
            case 'sms_bind':
                $description = '手机绑定';
                break;
            case 'sms_registration':
                $description = '用户注册';
                break;
            case 'sms_forget_password':
                $description = '登录密码重置';
                break;
            case 'sms_user_pay':
                $description = '网站余额支付';
                break;
            case 'sms_forget_pay_password':
                $description = '支付密码重置';
                break;
            case 'system_remind':
                $description = '直播公开课';
                break;
            default:
                $description = '';
                break;
        }

        return $description;
    }

    protected function checkErrorMsg($user, $currentTime, $smsType, Request $request)
    {
        $errorMsg = '';
        $to = $request->get('to');
        $this->checkSmsType($smsType, $user);
        if ('1' != $this->setting('cloud_sms.sms_enabled')) {
            $errorMsg = '短信服务未开启，请联系网校管理员';

            return $errorMsg;
        }

        if (!in_array($smsType, array('sms_registration', 'sms_user_pay', 'system_remind', 'sms_bind', 'sms_forget_pay_password', 'sms_forget_password'))) {
            if ($this->validateCaptcha($request)) {
                return '验证码错误';
            }
        }

        $targetSession = $request->getSession()->get($smsType);
        $smsLastTime = $targetSession['sms_last_time'];
        $allowedTime = 120;

        if (!$this->checkLastTime($smsLastTime, $currentTime, $allowedTime)) {
            $errorMsg = '请等待120秒再申请';

            return $errorMsg;
        }

        if (in_array($smsType, array('sms_bind', 'sms_registration'))) {
            if ('sms_bind' == $smsType) {
                $description = '手机绑定';
            } else {
                $description = '用户注册';
            }

            $hasVerifiedMobile = (isset($user['verifiedMobile']) && (strlen($user['verifiedMobile']) > 0));

            if ($hasVerifiedMobile && ($to == $user['verifiedMobile'])) {
                $errorMsg = '您已经绑定了该手机号码';

                return  $errorMsg;
            }

            if (!$this->getUserService()->isMobileUnique($to)) {
                $errorMsg = '该手机号码已被其他用户绑定';

                return  $errorMsg;
            }
        }

        if ('sms_forget_password' == $smsType) {
            $description = '登录密码重置';
            $targetUser = $this->getUserService()->getUserByVerifiedMobile($to);

            if (empty($targetUser)) {
                $errorMsg = '用户不存在';

                return  $errorMsg;
            }

            if ((!isset($targetUser['verifiedMobile']) || (0 == strlen($targetUser['verifiedMobile'])))) {
                $errorMsg = '用户没有被绑定的手机号';

                return  $errorMsg;
            }

            if ($targetUser['verifiedMobile'] != $to) {
                $errorMsg = '手机与用户名不匹配';

                return  $errorMsg;
            }
        }

        if (in_array($smsType, array('sms_user_pay', 'sms_forget_pay_password'))) {
            if ('sms_user_pay' == $smsType) {
                $description = '网站余额支付';
            } else {
                $description = '支付密码重置';
            }

            if ((!isset($user['verifiedMobile']) || (0 == strlen($user['verifiedMobile'])))) {
                $errorMsg = '用户没有被绑定的手机号';

                return  $errorMsg;

                //                return $this->createJsonResponse(array('error' => '用户没有被绑定的手机号'));
            }

            if ($user['verifiedMobile'] != $request->request->get('to')) {
                $errorMsg = '您输入的手机号，不是已绑定的手机';

                return  $errorMsg;
            }
        }

        if (!$this->checkPhoneNum($to)) {
            $errorMsg = sprintf('手机号错误:%s', $to);

            return  $errorMsg;
        }

        $currentUser = $this->getCurrentUser();

        if ($currentUser->isLogin()) {
            $key = $currentUser['email'];
        } else {
            $key = $to.$request->getClientIp();
        }

        // send 6 times in an hour
        $maxAllowance = $this->getRateLimiter($smsType, 6, 3600)->check($key);

        if (0 == $maxAllowance) {
            $errorMsg = '暂停发送验证码短信，请稍后再试';

            return $errorMsg;
        }

        return $errorMsg;
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->getBiz()->service('System:LogService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
    }

    protected function getSignEncoder()
    {
        return new MessageDigestPasswordEncoder('sha256');
    }

    protected function getRateLimiter($id, $maxAllowance, $period)
    {
        $factory = $this->getBiz()->offsetGet('ratelimiter.factory');

        return $factory($id, $maxAllowance, $period);
    }

    private function sendSms(Request $request, $smsType)
    {
        $currentUser = $this->getCurrentUser();
        $currentTime = time();
        $to = $request->get('to');

        $errorMsg = $this->checkErrorMsg($currentUser, $currentTime, $smsType, $request);

        if (!empty($errorMsg)) {
            return array('error' => $errorMsg);
        }

        $description = $this->generateDescription($smsType);
        $smsCode = $this->generateSmsCode();

        try {
            $api = CloudAPIFactory::create('leaf');
            $result = $api->post("/sms/{$api->getAccessKey()}/sendVerify", array(
                    'mobile' => $to,
                    'category' => $smsType,
                    'sendStyle' => 'templateId',
                    'description' => $description,
                    'verify' => $smsCode,
                ));
        } catch (\Exception $e) {
            $message = $e->getMessage();

            return array('error' => sprintf('发送失败, %s', $message));
        }
        if (isset($result['error'])) {
            return array('error' => sprintf('发送失败, %s', $result['error']));
        }

        $result['to'] = $to;
        $result['smsCode'] = $smsCode;
        $result['userId'] = $currentUser['id'];

        if (0 != $currentUser['id']) {
            $result['nickname'] = $currentUser['nickname'];
        }

        $this->getLogService()->info('sms', $smsType, sprintf('userId:%s,对%s发送用于%s的验证短信%s', $currentUser['id'], $to, $smsType, $smsCode), $result);

        $request->getSession()->set($smsType, array(
            'to' => $to,
            'sms_code' => $smsCode,
            'sms_last_time' => $currentTime,
        ));

        if ($currentUser->isLogin()) {
            $key = $currentUser['email'];
        } else {
            $key = $to.$request->getClientIp();
        }

        $maxAllowance = $this->getRateLimiter($smsType, 6, 3600)->getAllow($key);

        return array('ACK' => 'ok', 'allowance' => ($maxAllowance > 3) ? 0 : $maxAllowance);
    }

    private function validateDragCaptcha(Request $request)
    {
        $biz = $this->getBiz();
        $bizDragCaptcha = $biz['biz_drag_captcha'];
        $captcha = $request->request->get('captcha_num', '');
        $bizDragCaptcha->check($captcha);

        return true;
    }

    private function validateCaptcha(Request $request)
    {
        $captchaNum = strtolower($request->request->get('captcha_num'));

        return !empty($captchaNum) && $request->getSession()->get('captcha_code') == $captchaNum;
    }
}
