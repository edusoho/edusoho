<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Gregwar\Captcha\CaptchaBuilder;
use Symfony\Component\HttpFoundation\Response;

class ImgCodes extends BaseResource
{
    public function post(Application $app, Request $request)
    {
        $mobile = $request->request->get('mobile');

        if (empty($mobile)) {
            return $this->error('500', '手机号为空');
        }

        $user = $this->getUserService()->getUserByVerifiedMobile($mobile);
        if (!$user) {
            return $this->error('500', '该手机号未绑定用户');
        }

        $imgToken = $this->getTokenService()->makeToken('sms_registration', array(
            'times'    => 5,
            'duration' => 60 * 2,
            'userId'   => $user['id'],
            'data'     => array(
                'img_code' => $result['captcha_code'],
                'mobile'   => $mobile
            )
        ));

        $imgBuilder = new CaptchaBuilder;
        $imgBuilder->build($width = 150, $height = 32, $font = null);

        ob_start();
        $imgBuilder->output();
        $str = ob_get_clean();
        $imgBuilder = null;
        $headers = array(
            'Content-type'        => 'image/jpeg',
            'Content-Disposition' => 'inline; filename="'."img_captcha.jpg".'"',
        );

        return array('' => $str, '' => $token);
    }

    public function filter($res)
    {
        return $res;
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }
}
