<?php

namespace Topxia\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\File;


class CdnSettingController extends BaseController
{

    public function indexAction(Request $request)
    {
        $cdn = $this->getSettingService()->get('cdn', array());

        $default = array(
            'enabled' => '',
            'url' => '',
        );

        $cdn = array_merge($default, $cdn);

        if ($request->getMethod() == 'POST') {
            $cdn = $request->request->all();
            $this->getSettingService()->set('cdn', $cdn);
            $this->getLogService()->info('system', 'update_settings', "CDN设置", $cdn);
            $this->setFlashMessage('success', 'CDN设置已保存！');
        }

        return $this->render('TopxiaAdminBundle:System:cdn-setting.html.twig', array(
            'cdn'=>$cdn
        ));

    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

}