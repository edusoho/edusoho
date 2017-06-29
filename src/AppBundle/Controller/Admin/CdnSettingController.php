<?php

namespace AppBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;

class CdnSettingController extends BaseController
{
    public function indexAction(Request $request)
    {
        $cdn = $this->getSettingService()->get('cdn', array());

        $default = array(
            'enabled' => '',
            'defaultUrl' => '',
            'userUrl' => '',
            'contentUrl' => '',
        );

        $cdn = array_merge($default, $cdn);

        if ($request->getMethod() == 'POST') {
            $cdn = $request->request->all();
            $this->getSettingService()->set('cdn', $cdn);
            $this->getLogService()->info('system', 'update_settings', 'CDN设置', $cdn);
            $this->setFlashMessage('success', 'site.save.success');
        }

        return $this->render('admin/system/cdn-setting.html.twig', array(
            'cdn' => $cdn,
        ));
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
