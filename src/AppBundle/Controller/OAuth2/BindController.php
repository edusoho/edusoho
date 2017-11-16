<?php

namespace AppBundle\Controller\OAuth2;

use ApiBundle\Api\Resource\Setting\Setting;
use AppBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;

class BindController extends BaseController
{
    public function indexAction(Request $request)
    {
        $setting = new Setting($this->container, $this->getBiz());

        $registerSetting = $setting->getRegister();

        return ;
    }
}
