<?php

namespace AppBundle\Controller\OAuth2;

use ApiBundle\Api\Resource\Setting\Setting;
use AppBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;

class LoginController extends BaseController
{
    public function indexAction(Request $request)
    {
        $setting = new Setting($this->container, $this->getBiz());

        $registerSetting = $setting->getRegister();

        return $this->render('oauth2/index.html.twig', array(
            'mode' => $registerSetting['mode'],
        ));
    }

    public function bindAction(Request $request)
    {
        return $this->render('wap/third-party/third-party-login-bind-accout.html.twig', array(
        ));
    }

    public function successAction(Request $request)
    {
        return $this->render('wap/third-party/third-party-login-success.html.twig', array(
        ));
    }

    public function createAction(Request $request)
    {
        return $this->render('wap/third-party/third-party-login-create-accout.html.twig', array(
        ));
    }
}
