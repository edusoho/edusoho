<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Gregwar\Captcha\CaptchaBuilder;
use Symfony\Component\HttpFoundation\Response;

class ImgCodes extends BaseResource
{

    protected $imgBuilder;

    public function post(Application $app, Request $request)
    {
        $this->imgBuilder = new CaptchaBuilder;
        $str = $this->buildImg();

        $imgToken = $this->getTokenService()->makeToken('img_verify', array(
            'times'    => 5,
            'duration' => 60 * 2,
            'userId'   => 0,
            'data'     => array(
                'img_code' => $this->imgBuilder->getPhrase(),
            )
        ));

        $this->imgBuilder = null;
        
        return array('img_code' => $str, 'img_token' => $imgToken['token']);
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

    protected function getTokenService()
    {
        return $this->getServiceKernel()->createService('User.TokenService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }
}
