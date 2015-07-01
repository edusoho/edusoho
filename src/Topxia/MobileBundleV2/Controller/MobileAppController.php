<?php

namespace Topxia\MobileBundleV2\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Topxia\WebBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;

class MobileAppController extends MobileAppBaseController
{
	public function indexAction(Request $request)
	{
		$clientType = "pc";
		$userAgent = $request->headers->get("user-agent");
		$debug = "release";

		$render  = "TopxiaMobileBundleV2:ESMobile:main.index-{$debug}.html.twig";
		if (!strpos($userAgent, "kuozhi") ) {
			return $this->render($render, array("clientType"=>$clientType));
		}

		if (strpos($userAgent, "iPhone") || strpos($userAgent, "iPad")) {
			$clientType = "iOS";
		} else if (strpos($userAgent, "Android")) {
		           $clientType = "Android";
		}
		return $this->render($render, array("clientType"=>$clientType));
	}

	public function versionAction(Request $request)
	{
		$clientType = "iOS";
		$userAgent = $request->headers->get("user-agent");
        		if (strpos($userAgent, "iPhone") || strpos($userAgent, "iPad")) {
            		$clientType = "iOS";
        		} else if (strpos($userAgent, "Android")) {
            		$clientType = "Android";
        		}

        		$main = array();
        		$appDir = dirname(__DIR__);
        		$versionFile = $appDir . "/Resources/public/main/version.json";
        		if (file_exists($versionFile)) {
        			$main = json_decode(file_get_contents($versionFile));
        		}
        		
		$host = $request->getSchemeAndHttpHost();
	        	return $this->createJson($request, $main);
	}
}