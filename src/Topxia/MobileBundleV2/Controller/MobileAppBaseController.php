<?php

namespace Topxia\MobileBundleV2\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Topxia\WebBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;

abstract class MobileAppBaseController extends MobileBaseController
{
	public abstract function versionAction(Request $request);

	public function resourceAction(Request $request, $code)
	{
        		$userAgent = $request->headers->get("user-agent");
        		$clientType = "iOS";
        		if (strpos($userAgent, "iPhone") || strpos($userAgent, "iPad")) {
            		$clientType = "iOS";
        		} else if (strpos($userAgent, "Android")) {
            		$clientType = "Android";
        		}

        		$assets = $this->container->get('templating.helper.assets');
        		return $this->redirect($assets->getUrl("bundles/topxiamobilebundlev2/{$code}/release/{$clientType}.zip"));
	}
}