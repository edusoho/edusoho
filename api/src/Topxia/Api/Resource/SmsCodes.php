<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Topxia\Api\Util\ImgCodeUtil;
use Gregwar\Captcha\CaptchaBuilder;
use Symfony\Component\HttpFoundation\Request;

class SmsCodes extends BaseResource
{
    protected $imgBuilder;

    public function post(Application $app, Request $request)
    {
        $fields = $request->request->all();

        $biz     = $this->getServiceKernel()->getBiz();
        $factory = $biz['ratelimiter.factory'];
        $limiter = $factory('ip', 5, 86400);

        $remain = $limiter->check($request->getClientIp());

        if ($remain == 0 && empty($fields['img_code']) && empty($fields['verified_token'])) {
            $this->imgBuilder = new CaptchaBuilder;
            $str              = $this->buildImg();

            $imgToken = $this->getTokenService()->makeToken('img_verify', array(
                'times'    => 5,
                'duration' => 60 * 30,
                'userId'   => 0,
                'data'     => array(
                    'img_code' => $this->imgBuilder->getPhrase()
                )
            ));

            $this->imgBuilder = null;

            return array('img_code' => $str, 'verified_token' => $imgToken['token'], 'status' => 'limited');
        }

        if (isset($fields['img_code']) && !isset($fields['verified_token'])) {
            return $this->error('500', '非法请求');
        }

        if (!isset($fields['img_code']) && isset($fields['verified_token'])) {
            return $this->error('500', '非法请求');
        }

        if (isset($fields['img_code']) && isset($fields['verified_token'])) {
            $imgCode  = $request->request->get('img_code');
            $imgToken = $request->request->get('verified_token');

            if (empty($fields['img_code'])) {
                return $this->error('500', '图形验证码为空');
            }

            if (empty($fields['verified_token'])) {
                return $this->error('500', 'Token为空');
            }

            $imgCodeUtil = new ImgCodeUtil();
            try {
                $imgCodeUtil->verifyImgCode('img_verify', $imgCode, $imgToken);
            } catch (\Exception $e) {
                return $this->error('500', $e->getMessage());
            }
        }

        $type   = $fields['type'];
        $mobile = $fields['mobile'];

        if (!in_array($type, array('sms_change_password', 'sms_verify_mobile', 'sms_bind', 'sms_third_registration'))) {
            return $this->error('500', '短信服务不支持该业务');
        }
        if (empty($mobile)) {
            return $this->error('500', '手机号为空');
        }

        if ($type == 'sms_bind') {
            try {
                if ($this->getUserService()->getUserByVerifiedMobile($mobile)) {
                    throw new \Exception("该手机号已被绑定");
                }
                $user = $this->getCurrentUser();
                if (!$user->isLogin()) {
                    return $this->error('500', '用户没有登录');
                }

                $result = $this->getSmsService()->sendVerifySms('sms_bind', $mobile);
            } catch (\Exception $e) {
                return $this->error('500', $e->getMessage());
            }
        }

        if ($type == 'sms_change_password') {
            try {
                $result = $this->getSmsService()->sendVerifySms('sms_forget_password', $mobile);
            } catch (\Exception $e) {
                return $this->error('500', $e->getMessage());
            }
        }

        if ($type == 'sms_verify_mobile') {
            try {
                $result = $this->getSmsService()->sendVerifySms('sms_bind', $mobile);
            } catch (\Exception $e) {
                return $this->error('500', $e->getMessage());
            }
        }
        if ($type == 'sms_bind') {
            $type = 'sms_change_password';
        }

        if (isset($result['error'])) {
            return $this->error('500', $result['error']);
        }

        $smsToken = $this->getTokenService()->makeToken($type, array(
            'times'    => 5,
            'duration' => 60 * 30,
            'userId'   => 0,
            'data'     => array(
                'sms_code' => $result['captcha_code'],
                'mobile'   => $mobile
            )
        ));

        return array(
            'mobile'         => $mobile,
            'verified_token' => $smsToken['token'],
            'status'         => 'ok'
        );
    }

    public function filter($res)
    {
        return $res;
    }

    protected function buildImg()
    {
        $this->imgBuilder->build($width = 150, $height = 32, $font = null);

        ob_start();
        $this->imgBuilder->output();
        $str = ob_get_clean();

        return base64_encode($str);
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    protected function getTokenService()
    {
        return $this->createService('User:TokenService');
    }

    protected function getSmsService()
    {
        return $this->createService('Sms:SmsService');
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }
}
