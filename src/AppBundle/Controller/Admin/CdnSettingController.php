<?php

namespace AppBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\File;
use Topxia\Service\Common\ServiceKernel;


class CdnSettingController extends BaseController
{

    public function indexAction(Request $request)
    {
        $cdn = $this->getSettingService()->get('cdn', array());

        $default = array(
            'enabled' => '',
            'defaultUrl' => '',
            'userUrl'  => '',
            'contentUrl' => ''
        );

        $cdn = array_merge($default, $cdn);

        if ($request->getMethod() == 'POST') {
            $cdn = $request->request->all();
            $this->getSettingService()->set('cdn', $cdn);
            $this->getLogService()->info('system', 'update_settings', 'CDN设置', $cdn);
            $this->setFlashMessage('success', $this->getServiceKernel()->trans('CDN设置已保存！'));
        }

        return $this->render('admin/system/cdn-setting.html.twig', array(
            'cdn'=>$cdn
        ));

    }

    protected function getSettingService()
    {
        return ServiceKernel::instance()->createService('admin/system/SettingService');
    }

}