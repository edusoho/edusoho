<?php

namespace Topxia\WebBundle\Controller;

use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\JsonResponse;
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

        return $this->createJson($request, $info);
    }

    protected function createJson(Request $request, $data)
    {
        $callback = $request->query->get('callback');
        if ($callback) {
            return $this->createJsonP($request, $callback, $data);
        } else {
            return new JsonResponse($data);
        }
    }

    protected function createJsonP(Request $request, $callback, $data)
    {
        $response = new JsonResponse($data);
        $response->setCallback($callback);
        return $response;
    }
}