<?php

namespace Topxia\WebBundle\Controller;

use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use Topxia\System;

class SysteminfoController extends BaseController
{
    public function indexAction (Request $request)
    {
        $info = array(
            'version' => System::VERSION,
            'mobileApiVersion' => '1',
            'mobileApiUrl' => $request->getSchemeAndHttpHost() . '/mapi_v1',
        );

        return $this->createJsonResponse($info);
    }
}