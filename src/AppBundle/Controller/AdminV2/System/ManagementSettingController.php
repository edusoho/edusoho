<?php

namespace AppBundle\Controller\AdminV2\System;

use AppBundle\Controller\AdminV2\BaseController;
use Biz\System\Service\SettingService;
use Symfony\Component\HttpFoundation\Request;

class ManagementSettingController extends BaseController
{
    public function qualificationAction(Request $request)
    {
        $qualifications = $this->getSettingService()->get('qualifications', []);
        $default = [
            'icp' => '',
            'icpUrl' => 'https://beian.miit.gov.cn',
            'recordPicture' => '',
            'recordCode' => '',
            'recordUrl' => 'http://www.beian.gov.cn/portal/registerSystemInfo?recordcode=',
        ];
        $qualifications = array_merge($default, $qualifications);

        return $this->render('admin-v2/system/management/index.html.twig', [
            'qualifications' => $qualifications,
        ]);
    }

    public function licenseAction(Request $request)
    {
        return $this->render('admin-v2/system/management/license.html.twig', [
        ]);
    }

    public function saveQualificationAction(Request $request)
    {
        $qualifications = $request->request->all();
        $site = $this->getSettingService()->get('site', []);
        $site = array_replace($site, $qualifications);

        $site['recordCode'] = trim($site['recordCode']);

        $this->getSettingService()->set('qualifications', $qualifications);
        $this->getSettingService()->set('site', $site);

        return $this->createJsonResponse([
            'message' => $this->trans('site.save.success'),
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
