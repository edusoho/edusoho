<?php

namespace Mooc\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\MobileBundleV2\Controller\MobileAppController;

class MoocMobileAppController extends MobileAppController
{
    public function indexAction(Request $request)
    {
        $userAgent  = $request->headers->get("user-agent");
        $clientType = $this->getClientType($userAgent);
        $debug      = "debug";

        $render = "MoocWebBundle:ESMobile:main.index-{$debug}.html.twig";

        if (!strpos($userAgent, "kuozhi")) {
            return $this->render($render, array("clientType" => "pc"));
        }

        return $this->render($render, array("clientType" => $clientType));
    }

    private function getClientType($userAgent)
    {
        $clientType = "Android";

        if (strpos($userAgent, "iPhone") || strpos($userAgent, "iPad")) {
            $clientType = "iOS";
        } elseif (strpos($userAgent, "Android")) {
            $clientType = "Android";
        }

        return $clientType;
    }
}
