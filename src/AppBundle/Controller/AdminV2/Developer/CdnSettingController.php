<?php

namespace AppBundle\Controller\AdminV2\Developer;

use AppBundle\Controller\AdminV2\BaseController;
use Biz\System\Service\SettingService;
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

        if ('POST' == $request->getMethod()) {
            $cdn = $request->request->all();
            $this->getSettingService()->set('cdn', $cdn);
            $this->getLogService()->info('system', 'update_settings', 'CDN设置', $cdn);
            $this->setFlashMessage('success', 'site.save.success');
        }

        return $this->render('admin-v2/developer/cdn/cdn-setting.html.twig', array(
            'cdn' => $cdn,
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
