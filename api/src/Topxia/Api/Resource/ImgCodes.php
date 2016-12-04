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


        $response = new Response($str, 200, $headers);
        $response->send();
    }

    public function filter($res)
    {
        return $res;
    }
}
