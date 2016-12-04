<?php 

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Gregwar\Captcha\CaptchaBuilder;
use Symfony\Component\HttpFoundation\Response;

class SmsCodes extends BaseResource
{
    public function post(Application $app, Request $request)
    {
        $biz = $this->getServiceKernel()->getBiz();
        $factory = $biz['ratelimiter.factory'];
        $limiter = $factory('ip', 10, 600);

        $remain = $limiter->check($request->getClientIp());
        if ($remain == 0) {
            // $imgBuilder = new CaptchaBuilder;
            // $imgBuilder->build($width = 150, $height = 32, $font = null);

            // ob_start();
            // $imgBuilder->output();
            // $str = ob_get_clean();
            // $imgBuilder = null;
            // $headers = array(
            //     'Content-type'        => 'image/jpeg',
            //     'Content-Disposition' => 'inline; filename="'."img_captcha.jpg".'"',
            // );


            // $response = new Response($str, 200, $headers);
            // $response->send();
            $imgCaptcha = $request->request->get('img_captcha_code');

            if (empty($imgCaptcha)) {
                return $this->error('500', '图形验证码为空');
            }
        }

        $type   = $request->request->get('type');
        $mobile = $request->request->get('mobile');

        if (!in_array($type, array('sms_change_password', 'sms_verify_mobile'))) {
            return $this->error('500', '短信服务不支持该业务');
        }
        if (empty($mobile)) {
            return $this->error('500', '手机号为空');
        }

        if ($type == 'sms_change_password') {
            try {
                $result = $this->getSmsService()->sendVerifySms('sms_forget_password', $mobile);
            } catch(Exception $e) {
                return $this->error('500', $e->getMessage());
            }
        }

        if ($type == 'sms_verify_mobile') {
            try {
                $result = $this->getSmsService()->sendVerifySms('sms_bind', $mobile);
            } catch(Exception $e) {
                return $this->error('500', $e->getMessage());
            }
        }
            
        $user = $this->getCurrentUser();
        $smsToken = $this->getTokenService()->makeToken($type, array(
            'times'    => 5,
            'duration' => 60 * 2,
            'userId'   => $user['id'],
            'data'     => array(
                'sms_code' => $result['captcha_code'],
                'mobile'   => $mobile
            )
        ));

        return array(
            'mobile'   => $mobile,
            'smsToken' => $smsToken['token']
        );
    }

    public function filter($res)
    {
        return $res;
    }

    protected function getTokenService()
    {
        return $this->getServiceKernel()->createService('User.TokenService');
    }

    protected function getSmsService()
    {
        return $this->getServiceKernel()->createService('Sms.SmsService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }
}