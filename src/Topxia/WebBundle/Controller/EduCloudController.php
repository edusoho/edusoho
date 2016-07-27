<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\CloudPlatform\CloudAPIFactory;
use Topxia\Service\Sms\SmsProcessor\SmsProcessorFactory;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;

class EduCloudController extends BaseController
{
    public function smsSendAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            if ($this->setting('cloud_sms.sms_enabled') != '1') {
                return $this->createJsonResponse(array('error' => '短信服务被管理员关闭了'));
            }

            $currentUser = $this->getCurrentUser();
            $currentTime = time();

            $smsType = $request->request->get('sms_type');
            $this->checkSmsType($smsType, $currentUser);

            if (!in_array($smsType, array('sms_user_pay', 'system_remind'))) {
                $captchaNum = strtolower($request->request->get('captcha_num'));

                if ($request->getSession()->get('captcha_code') != $captchaNum) {
                    return $this->createJsonResponse(array('error' => '验证码错误'));
                }
            }

            $targetSession = $request->getSession()->get($smsType);
            $smsLastTime   = $targetSession['sms_last_time'];
            $allowedTime   = 120;

            if (!$this->checkLastTime($smsLastTime, $currentTime, $allowedTime)) {
                return $this->createJsonResponse(array('error' => '请等待120秒再申请', 'message' => "{$smsLastTime}|{$currentTime}"));
            }

            if (in_array($smsType, array('sms_bind', 'sms_registration'))) {
                $to = $request->request->get('to');

                if ($smsType == 'sms_bind') {
                    $description = '手机绑定';
                } else {
                    $description = '用户注册';
                }

                $hasVerifiedMobile = (isset($currentUser['verifiedMobile']) && (strlen($currentUser['verifiedMobile']) > 0));

                if ($hasVerifiedMobile && ($to == $currentUser['verifiedMobile'])) {
                    return $this->createJsonResponse(array('error' => "您已经绑定了该手机号码"));
                }

                if (!$this->getUserService()->isMobileUnique($to)) {
                    return $this->createJsonResponse(array('error' => "该手机号码已被其他用户绑定"));
                }
            }

            if ($smsType == 'sms_forget_password') {
                $description = '登录密码重置';
                $targetUser  = $this->getUserService()->getUserByVerifiedMobile($request->request->get('to'));

                if (empty($targetUser)) {
                    return $this->createJsonResponse(array('error' => '用户不存在'));
                }

                if ((!isset($targetUser['verifiedMobile']) || (strlen($targetUser['verifiedMobile']) == 0))) {
                    return $this->createJsonResponse(array('error' => '用户没有被绑定的手机号'));
                }

                if ($targetUser['verifiedMobile'] != $request->request->get('to')) {
                    return $this->createJsonResponse(array('error' => '手机与用户名不匹配'));
                }

                $to = $targetUser['verifiedMobile'];
            }

            if (in_array($smsType, array('sms_user_pay', 'sms_forget_pay_password'))) {
                $user = $currentUser->toArray();

                if ($smsType == 'sms_user_pay') {
                    $description = '网站余额支付';
                } else {
                    $description = '支付密码重置';
                }

                if ((!isset($user['verifiedMobile']) || (strlen($user['verifiedMobile']) == 0))) {
                    return $this->createJsonResponse(array('error' => '用户没有被绑定的手机号'));
                }

                if ($user['verifiedMobile'] != $request->request->get('to')) {
                    return $this->createJsonResponse(array('error' => '您输入的手机号，不是已绑定的手机'));
                }

                $to = $user['verifiedMobile'];
            }

            if ($smsType == 'system_remind') {
                $to          = $request->request->get('to');
                $description = '直播公开课';
            }

            if (!$this->checkPhoneNum($to)) {
                return $this->createJsonResponse(array('error' => "手机号错误:{$to}"));
            }

            $smsCode = $this->generateSmsCode();

            try {
                $api    = CloudAPIFactory::create('leaf');
                $result = $api->post("/sms/{$api->getAccessKey()}/sendVerify", array('mobile' => $to, 'category' => $smsType, 'description' => $description, 'verify' => $smsCode));

                if (isset($result['error'])) {
                    return $this->createJsonResponse(array('error' => "发送失败, {$result['error']}"));
                }
            } catch (\RuntimeException $e) {
                $message = $e->getMessage();
                return $this->createJsonResponse(array('error' => "发送失败, {$message}"));
            }

            $result['to']      = $to;
            $result['smsCode'] = $smsCode;
            $result['userId']  = $currentUser['id'];

            if ($currentUser['id'] != 0) {
                $result['nickname'] = $currentUser['nickname'];
            }

            $this->getLogService()->info('sms', $smsType, "userId:{$currentUser['id']},对{$to}发送用于{$smsType}的验证短信{$smsCode}", $result);

            $request->getSession()->set($smsType, array(
                'to'            => $to,
                'sms_code'      => $smsCode,
                'sms_last_time' => $currentTime
            ));

            return $this->createJsonResponse(array('ACK' => 'ok'));
        }

        return $this->createJsonResponse(array('error' => 'GET method'));
    }

    public function smsCheckAction(Request $request, $type)
    {
        $targetSession = $request->getSession()->get($type);

        if (strlen($request->query->get('value')) == 0 || strlen($targetSession['sms_code']) == 0) {
            $response = array('success' => false, 'message' => '验证码错误');
        }

        $mobile = $request->query->get('mobile') ? $request->query->get('mobile') : '';

        if ($mobile != '' && !empty($targetSession['to']) && $mobile != $targetSession['to']) {
            return $this->createJsonResponse(array('success' => false, 'message' => '验证码和手机号码不匹配'));
        }

        if ($targetSession['sms_code'] == $request->query->get('value')) {
            $response = array('success' => true, 'message' => '验证码正确');
        } else {
            $response = array('success' => false, 'message' => '验证码错误');
        }

        return $this->createJsonResponse($response);
    }

    public function cloudCallBackAction(Request $request)
    {
        $settings = $this->getSettingService()->get('storage', array());

        $data         = $request->request->all();
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
        $index      = $request->query->get('index');
        $smsType    = $request->query->get('smsType');
        $originSign = rawurldecode($request->query->get('sign'));

        $siteSetting        = $this->getSettingService()->get('site');
        $siteSetting['url'] = rtrim($siteSetting['url']);
        $siteSetting['url'] = rtrim($siteSetting['url'], '/');
        $url                = $siteSetting['url'];
        $url .= $this->generateUrl('edu_cloud_sms_send_callback', array('targetType' => $targetType, 'targetId' => $targetId));
        $url .= '?index='.$index.'&smsType='.$smsType;
        $api  = CloudAPIFactory::create('leaf');
        $sign = $this->getSignEncoder()->encodeSign($url, $api->getAccessKey());

        if ($originSign != $sign) {
            return $this->createJsonResponse(array('error' => 'sign不正确'));
        }

        $processor = SmsProcessorFactory::create($targetType);
        return $this->createJsonResponse($processor->getSmsInfo($targetId, $index, $smsType));
    }

    public function searchCallBackAction(Request $request)
    {
        $originSign = rawurldecode($request->query->get('sign'));

        $searchSetting = $this->getSettingService()->get('cloud_search');

        $siteSetting        = $this->getSettingService()->get('site');
        $siteSetting['url'] = rtrim($siteSetting['url']);
        $siteSetting['url'] = rtrim($siteSetting['url'], '/');

        $url = $siteSetting['url'];
        $url .= $this->generateUrl('edu_cloud_search_callback');
        $api  = CloudAPIFactory::create('root');
        $sign = $this->getSignEncoder()->encodeSign($url, $api->getAccessKey());

        if ($originSign != $sign) {
            return $this->createJsonResponse(array('error' => 'sign不正确'));
        }

        $searchSetting['search_enabled'] = 1;
        $searchSetting['status']         = 'ok';

        $this->getSettingService()->set('cloud_search', $searchSetting);

        return $this->createJsonResponse(true);
    }

    protected function generateSmsCode($length = 6)
    {
        $code = rand(0, 9);

        for ($i = 1; $i < $length; $i++) {
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
        if (!((strlen($smsLastTime) == 0) || (($currentTime - $smsLastTime) > $allowedTime))) {
            return false;
        }

        return true;
    }

    protected function checkSmsType($smsType, $user)
    {
        if (!in_array($smsType, array('sms_bind', 'sms_user_pay', 'sms_registration', 'sms_forget_password', 'sms_forget_pay_password', 'system_remind'))) {
            throw new \RuntimeException('不存在的sms Type');
        }

        if ((!$user->isLogin()) && (in_array($smsType, array('sms_bind', 'sms_user_pay', 'sms_forget_pay_password')))) {
            throw new \RuntimeException('用户未登录');
        }

        if ($this->setting("cloud_sms.{$smsType}") != 'on' && !$this->getUserService()->isMobileRegisterMode()) {
            throw new \RuntimeException('该使用场景未开启');
        }
    }

    protected function getLogService()
    {
        return $this->getServiceKernel()->createService('System.LogService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getSignEncoder()
    {
        return new MessageDigestPasswordEncoder('sha256');
    }
}
