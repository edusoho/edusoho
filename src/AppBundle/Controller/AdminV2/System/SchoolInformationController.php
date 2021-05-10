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
            'icpUrl' => 'https://beian.miit.gov.cn',
            'analytics' => '',
            'status' => 'open',
            'closed_note' => '',
            'favicon' => '',
            'copyright' => '',
            'recordPicture' => '',
            'recordCode' => '',
            'recordUrl' => 'http://www.beian.gov.cn/portal/registerSystemInfo?recordcode=',
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
        $qualifications = $this->getSettingService()->get('qualifications', []);
        $site = array_merge($site, $qualifications);
        $site['recordCode'] = trim($site['recordCode']);
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
