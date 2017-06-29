<?php

namespace Topxia\MobileBundleV2\Controller;

use AppBundle\System;
use Symfony\Component\HttpFoundation\Request;

class MobileAppController extends MobileBaseController
{
    const API_VERSIN = '1.6.0';

    public function indexAction(Request $request)
    {
        $userAgent = $request->headers->get('user-agent');
        $clientType = $this->getClientType($userAgent);
        $debug = 'debug';

        $render = "TopxiaMobileBundleV2:ESMobile:main.index-{$debug}.html.twig";
        if (!strpos($userAgent, 'kuozhi')) {
            return $this->render($render, array('clientType' => 'pc'));
        }

        return $this->render($render, array('clientType' => $clientType));
    }

    public function versionAction(Request $request)
    {
        $code = $request->query->get('code', 'edusoho');
        $userAgent = $request->headers->get('user-agent');
        $clientType = $this->getClientType($userAgent);

        $versionStr = $this->sendRequest('GET', "http://www.edusoho.com/version/{$code}-html5-main", array());

        if (empty($versionStr)) {
            return $this->createJson($request, array());
        }

        $main = (array) json_decode($versionStr);
        if (isset($main['error'])) {
            return $this->createJson($request, array());
        }

        $supportVersion = $main['support_version'];
        if (strnatcasecmp(System::VERSION, $supportVersion) < 0) {
            return $this->createJson($request, array());
        }
        $main['resource'] = $main[$clientType.'Url'];
        unset($main['AndroidUrl']);
        unset($main['iOSUrl']);

        return $this->createJson($request, $main);
    }

    private function getClientType($userAgent)
    {
        $clientType = 'Android';
        if (strpos($userAgent, 'iPhone') || strpos($userAgent, 'iPad')) {
            $clientType = 'iOS';
        } elseif (strpos($userAgent, 'Android')) {
            $clientType = 'Android';
        }

        return $clientType;
    }
}
