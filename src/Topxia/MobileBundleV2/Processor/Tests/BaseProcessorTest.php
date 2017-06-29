<?php

namespace Topxia\MobileBundleV2\Processor\Tests;

use Biz\BaseTestCase;
use Symfony\Component\HttpFoundation\Request;
use Topxia\MobileBundleV2\Controller\MobileApiController;

class BaseProcessorTest extends BaseTestCase
{
    protected function init()
    {
    }

    protected function getContent($requestData, $service, $method, $requestMethod)
    {
        $method = $this->getCurrentMethod($method);
        $requst = Request::create($service.'/'.$method, $requestMethod, $requestData);
        $controller = new MobileApiController();
        $result = $controller->indexAction($requst, $service, $method);

        $data = $result->getContent();

        return json_decode($data);
    }

    protected function getCurrentMethod($name)
    {
        if (empty($name)) {
            return '';
        }
        $method = substr($name, 4);

        return lcfirst($method);
    }
}
