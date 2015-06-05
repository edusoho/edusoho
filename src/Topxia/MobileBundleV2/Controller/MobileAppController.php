<?php

namespace Topxia\MobileBundleV2\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Topxia\WebBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;

class MobileAppController extends MobileBaseController
{
	public function indexAction(Request $request)
	{
		$clientType = "pc";
		$userAgent = $request->headers->get("user-agent");
		if (strpos($userAgent, "kuozhi") ) {
			return $this->render('TopxiaMobileBundleV2:ESMobile:index.html.twig', array("clientType"=>$clientType));
		}

		if (strpos($userAgent, "iPhone") || strpos($userAgent, "iPad")) {
			$clientType = "iOS";
		} else if (strpos($userAgent, "Android")) {
		           $clientType = "Android";
		}
		return $this->render('TopxiaMobileBundleV2:ESMobile:index.html.twig', array("clientType"=>$clientType));
	}
}