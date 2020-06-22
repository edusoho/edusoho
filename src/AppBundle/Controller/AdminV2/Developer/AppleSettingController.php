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
            $data = $request->request->all();
            $this->getSettingService()->set('apple_setting', $data);
            $this->getLogService()->info('system', 'update_settings', 'APPLE设置', $data);
            $this->setFlashMessage('success', 'site.save.success');
        }

        return $this->render('admin-v2/developer/apple/apple-setting.html.twig', array(
            'setting' => $setting,
        ));
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
