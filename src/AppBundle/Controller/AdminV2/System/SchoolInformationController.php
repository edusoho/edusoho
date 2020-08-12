<?php

namespace AppBundle\Controller\AdminV2\System;

use AppBundle\Controller\AdminV2\BaseController;
use Biz\Common\HTMLHelper;
use Biz\System\Service\SettingService;
use Symfony\Component\HttpFoundation\Request;

class SchoolInformationController extends BaseController
{
    public function siteAction(Request $request)
    {
        $site = $this->getSettingService()->get('site', []);
        $default = [
            'name' => '',
            'slogan' => '',
            'url' => '',
            'logo' => '',
            'seo_keywords' => '',
            'seo_description' => '',
            'master_email' => '',
            'icp' => '',
            'icpUrl' => 'http://www.beian.miit.gov.cn',
            'analytics' => '',
            'status' => 'open',
            'closed_note' => '',
            'favicon' => '',
            'copyright' => '',
        ];
        $site = array_merge($default, $site);

        return $this->render('admin-v2/system/school-information.html.twig', [
            'site' => $site,
        ]);
    }

    public function saveSiteAction(Request $request)
    {
        $site = $request->request->all();

        if (!empty($site['analytics'])) {
            $helper = new HTMLHelper($this->getBiz());
            $site['analytics'] = $helper->closeTags($site['analytics']);
        }
        $this->getSettingService()->set('site', $site);

        return $this->createJsonResponse([
            'message' => $this->trans('site.save.success'),
        ]);
    }

    public function licenseAction(Request $request)
    {
        $license = $this->getSettingService()->get('license', []);

        $default = [
            'license_name' => '',
            'license_picture' => '',
            'license_url' => '',
            'permits' => [
                ['name' => '', 'record_number' => '', 'picture' => ''],
            ],
        ];
        $license = array_merge($default, $license);

        return $this->render('admin-v2/system/certificates-setting.html.twig', [
            'license' => $license,
        ]);
    }

    public function saveLicenseAction(Request $request)
    {
        $license = $request->request->all();
        foreach ($license['permits'] as $key => $permit) {
            if ('' == $permit['name'] && '' == $permit['record_number'] && '' == $permit['picture'] && 0 != $key) {
                unset($license['permits'][$key]);
            }
        }

        if (!empty($license['analytics'])) {
            $helper = new HTMLHelper($this->getBiz());
            $site['analytics'] = $helper->closeTags($license['analytics']);
        }
        $this->getSettingService()->set('license', $license);

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
