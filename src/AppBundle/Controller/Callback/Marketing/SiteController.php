<?php

namespace AppBundle\Controller\Callback\Marketing;

use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\BaseController;

class SiteController extends BaseController
{
    public function siteInfoAction(Request $request)
    {
        $site = $this->getSettingService()->get('site', array());
        $site['logo'] = str_replace('files/', '', $site['logo']);
        $consult = $this->getSettingService()->get('consult', array());
        $consult['webchatURI'] = str_replace('files/', '', $consult['webchatURI']);

        $siteInfo = [
            'name' => $site['name'],
            'logo' => empty($site['logo']) ? '' : $this->getWebExtension()->getFurl($site['logo']),
            'about' => $site['slogan'],
            'wechat' => empty($consult['webchatURI']) ? '' : $this->getWebExtension()->getFurl($consult['webchatURI']),
            'qq' => empty($consult['qq']) ? '' : $consult['qq'][0]['number'],
            'telephone' => empty($consult['phone']) ? '' : $consult['phone'][0]['number']
        ];

        return $this->createJsonResponse($siteInfo);
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    protected function getWebExtension()
    {
        return $this->container->get('web.twig.extension');
    }
}
