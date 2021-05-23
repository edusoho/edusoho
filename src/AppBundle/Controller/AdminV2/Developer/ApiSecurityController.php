<?php

namespace AppBundle\Controller\AdminV2\Developer;

use AppBundle\Controller\AdminV2\BaseController;
use Biz\System\Service\SettingService;
use Symfony\Component\HttpFoundation\Request;

class ApiSecurityController extends BaseController
{
    public function settingAction(Request $request)
    {
        $setting = $this->getSettingService()->get('api_security', ['level' => 'close', 'client' => []]);
        if ('POST' === $request->getMethod()) {
            $setting = [
                'level' => $request->request->get('level'),
                'client' => $request->request->get('client'),
            ];
            $this->getSettingService()->set('api_security', $setting);
        }

        return $this->render('admin-v2/developer/api-security/setting.html.twig', [
            'apiSecuritySetting' => $setting,
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
