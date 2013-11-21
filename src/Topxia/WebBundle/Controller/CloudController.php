<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class CloudController extends BaseController
{
    public function setServerAction(Request $request)
    {
        $server = $request->query->get('server');
        $sign = $request->query->get('sign');

        if (empty($server)) {
            return $this->createJsonResponse(array('error' => 'server param is empty.'));
        }

        if (empty($sign)) {
            return $this->createJsonResponse(array('error' => 'sign param is empty.'));
        }

        $setting = $this->getSettingService()->get('storage', array());

        if (empty($setting['cloud_secret_key'])) {
            return $this->createJsonResponse(array('error' => 'secret key not set.'));
        }

        if (!$this->checkSign($server, $sign, $setting['cloud_secret_key'])) {
            return $this->createJsonResponse(array('error' => 'sign error.'));
        }

        $setting['cloud_api_server'] = $server;

        $this->getSettingService()->set('storage', $setting);

        return $this->createJsonResponse(true);
    }

    private function checkSign($server, $sign, $secretKey)
    {
        return md5($server . $secretKey) == $sign;
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

}