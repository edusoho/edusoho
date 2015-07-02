<?php

namespace Topxia\MobileBundleV2\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Topxia\WebBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;

class MobileAppController extends MobileAppBaseController
{
	public function indexAction(Request $request)
	{
		$userAgent = $request->headers->get("user-agent");
		$clientType = $this->getClientType($userAgent);
		$debug = "debug";

		$render  = "TopxiaMobileBundleV2:ESMobile:main.index-{$debug}.html.twig";
		if (!strpos($userAgent, "kuozhi") ) {
			return $this->render($render, array("clientType"=>$clientType));
		}

		return $this->render($render, array("clientType"=>$clientType));
	}

	public function versionAction(Request $request)
	{
		$userAgent = $request->headers->get("user-agent");
		$clientType = $this->getClientType($userAgent);

        		$main = array();
        		$appDir = dirname(__DIR__);
        		$versionFile = $appDir . "/Resources/public/main/version.json";
        		if (file_exists($versionFile)) {
        			$main = json_decode(file_get_contents($versionFile));
        		}

        		$host = $request->getSchemeAndHttpHost();
        		$main->resource = $host . "/bundles/topxiamobilebundlev2/main/release/{$clientType}.zip";
	        	return $this->createJson($request, $main);
	}

	private function getClientType($userAgent)
	{
		$clientType = "pc";
        		if (strpos($userAgent, "iPhone") || strpos($userAgent, "iPad")) {
            		$clientType = "iOS";
        		} else if (strpos($userAgent, "Android")) {
            		$clientType = "Android";
        		}

        		return $clientType;
	}
}