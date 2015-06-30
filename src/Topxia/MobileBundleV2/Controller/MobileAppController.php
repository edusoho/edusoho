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

		$host = $request->getSchemeAndHttpHost();
	        	$main = array(
		            "code"=>"main",
		            "icon"=>"",
		            "name"=>"移动App",
		            "description"=>"EduSoho官方移动App",
		            "author"=>"官方",
		            "version"=>"1.0.0",
		            "support_version"=>"6.0.0+",
		            "resource"=>$host . "/bundles/topxiamobilebundlev2/main/release/{$clientType}.zip",
		            "url"=>"mapi_v2/mobile/main"
	        	);
	        	return $this->createJson($request, $main);
	}
}