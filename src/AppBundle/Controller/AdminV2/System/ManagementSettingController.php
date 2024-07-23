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

    public function saveQualificationAction(Request $request)
    {
        $qualifications = $request->request->all();
        $site = $this->getSettingService()->get('site', []);
        $site = array_replace($site, $qualifications);

        $site['recordCode'] = trim($site['recordCode']);
        $qualifications['recordCode'] = trim($qualifications['recordCode']);

        $this->getSettingService()->set('qualifications', $qualifications);
        $this->getSettingService()->set('site', $site);

        return $this->createJsonResponse([
            'message' => $this->trans('site.save.success'),
        ]);
    }

    public function licenseAction(Request $request)
    {
        if ($request->isMethod('POST')) {
            $permits = $request->request->all();
            foreach ($permits['permits'] as $key => $permit) {
                if (empty($permit['name']) && empty($permit['record_number']) && empty($permit['picture']) && 0 != $key) {
                    unset($permits['permits'][$key]);
                    continue;
                }
                $permits['permits'][$key]['name'] = trim($permit['name']);
                $permits['permits'][$key]['picture'] = preg_replace('/^(.*\/)files/', '/files', $permits['permits'][$key]['picture']);
            }
            $permits['license_name'] = trim($permits['license_name']);
            $this->getSettingService()->set('license', $permits);
            $this->getSettingService()->set('permits', $permits);
        }
        $default = [
            'license_name' => '',
            'license_picture' => '',
            'license_url' => '',
            'permits' => [
                ['name' => '', 'record_number' => '', 'picture' => ''],
            ],
        ];
        $permits = $this->getSettingService()->get('permits', []);

        $permits = array_merge($default, $permits);

        return $this->render('admin-v2/system/management/license.html.twig', [
            'license' => $permits,
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
