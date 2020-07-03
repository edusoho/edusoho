<?php

namespace AppBundle\Controller\AdminV2\Developer;

use AppBundle\Controller\AdminV2\BaseController;
use Biz\System\Service\SettingService;
use Symfony\Component\HttpFoundation\Request;

class AppleSettingController extends BaseController
{
    public function indexAction(Request $request)
    {
        $setting = $this->getSettingService()->get('apple_setting', []);

        if ('POST' == $request->getMethod()) {
            $setting = $request->request->all();
            $this->getSettingService()->set('apple_setting', $setting);
            $this->getLogService()->info('system', 'update_settings', 'APPLE设置', $setting);
        }

        return $this->render('admin-v2/developer/apple/apple-setting.html.twig', [
            'setting' => $setting,
        ]);
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
