<?php

namespace AppBundle\Controller\AdminV2\CloudCenter;

use AppBundle\Controller\AdminV2\BaseController;
use Biz\CloudPlatform\Service\AppService;
use Biz\System\Service\SettingService;
use Biz\Xapi\Service\XapiService;
use Symfony\Component\HttpFoundation\Request;

class DataLabController extends BaseController
{
    public function dataAction(Request $request)
    {
        $url = $this->getAppService()->getTokenLoginUrl('data_lab_esiframe', array(), $request->isSecure());

        return $this->render('admin-v2/cloud-center/data-lab/data.html.twig', array(
            'url' => $url,
        ));
    }

    public function setttingAction(Request $request)
    {
        return $this->render('admin-v2/cloud-center/data-lab/setting.html.twig');
    }

    public function enableAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            $this->setSiteTrace(1);

            return $this->setXapiSetting(1);
        }

        return $this->render('admin-v2/cloud-center/data-lab/open-setting.html.twig');
    }

    public function disableAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            $this->setSiteTrace(0);

            return $this->setXapiSetting(0);
        }

        return $this->render('admin-v2/cloud-center/data-lab/close-setting.html.twig');
    }

    private function setSiteTrace($enable)
    {
        $siteTraceSetting = $this->getSettingService()->get('siteTrace', array());

        $biz = $this->getBiz();
        $siteSetting = $this->getSettingService()->get('site', array());
        $result = $biz['qiQiuYunSdk.esOp']->getTraceScript(array(
            'site_name' => empty($siteSetting['name']) ? 'EDUSOHO测试站' : $siteSetting['name'],
            'domain' => $this->getRequest()->getHost(),
            'enable' => $enable,
        ));

        $magic = $this->getSettingService()->get('magic', array());

        $enable = isset($magic['site_trace_enable']) ? $magic['site_trace_enable'] : $enable;

        $siteTraceSetting = array(
            'enabled' => $enable,
            'script' => $result['script'],
        );

        $this->getSettingService()->set('siteTrace', $siteTraceSetting);
    }

    private function setXapiSetting($enable)
    {
        $xapiSdk = $this->getXapiService()->getXapiSdk();
        $xapiSdkValue = 0 === $enable ? false : true;
        $xapiSdk->setting('xapiUpload', $xapiSdkValue);

        $xapiSetting = $this->getSettingService()->get('xapi', array());
        $xapiSetting['enabled'] = $enable;
        $xapiSetting = $this->getSettingService()->set('xapi', $xapiSetting);

        $user = $this->getUser();
        $logText = $xapiSdkValue ? 'xapi的设置上报开启' : 'xapi的上报关闭';
        $this->getLogService()->info('datalab', 'set_xapi_setting', $logText, array('enabled' => $enable, 'userId' => $user['id']));

        return $this->createJsonResponse(array('success' => 1));
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return XapiService
     */
    protected function getXapiService()
    {
        return $this->createService('Xapi:XapiService');
    }

    /**
     * @return AppService
     */
    protected function getAppService()
    {
        return $this->createService('CloudPlatform:AppService');
    }
}
