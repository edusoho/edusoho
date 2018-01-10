<?php

namespace AppBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use QiQiuYun\SDK\Auth;

class DataLabController extends BaseController
{
    public function dataAction(Request $request)
    {
        $url = $this->getAppService()->getTokenLoginUrl('data_lab_esiframe', array());

        return $this->render('admin/data-lab/data.html.twig', array(
            'url' => $url,
        ));
    }

    public function setttingAction(Request $request)
    {
        return $this->render('admin/data-lab/setting.html.twig');
    }

    public function enableXapiSettingAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            $this->setXapiSetting(1);
        }

        return $this->render('admin/data-lab/open-setting.html.twig'); 
    }

    public function disableXapiSettingAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            $this->setXapiSetting(0);
        }
        return $this->render('admin/data-lab/close-setting.html.twig'); 
    }

    private function setXapiSetting($enable)
    {
        $xapiSetting = $this->getSettingService()->get('xapi', array());
        $xapiSetting['enabled'] = $enable;
        $xapiSetting = $this->getSettingService()->set('xapi', $xapiSetting);
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    protected function getAppService()
    {
        return $this->createService('CloudPlatform:AppService');
    }
}