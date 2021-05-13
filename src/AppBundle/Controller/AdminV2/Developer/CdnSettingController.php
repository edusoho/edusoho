<?php

namespace AppBundle\Controller\AdminV2\Developer;

use AppBundle\Controller\AdminV2\BaseController;
use Biz\System\Service\SettingService;
use Symfony\Component\HttpFoundation\Request;

class CdnSettingController extends BaseController
{
    public function indexAction(Request $request)
    {
        $cdn = $this->getSettingService()->get('cdn', []);

        $default = [
            'enabled' => '',
            'defaultUrl' => '',
            'userUrl' => '',
            'contentUrl' => '',
        ];

        $cdn = array_merge($default, $cdn);

        if ('POST' == $request->getMethod()) {
            $cdn = $request->request->all();
            $this->getSettingService()->set('cdn', $cdn);
            $this->getLogService()->info('system', 'update_settings', 'CDN设置', $cdn);
            $this->addCdnUrlsToSecurity($cdn);
            $this->setFlashMessage('success', 'site.save.success');
        }

        return $this->render('admin-v2/developer/cdn/cdn-setting.html.twig', [
            'cdn' => $cdn,
        ]);
    }

    private function addCdnUrlsToSecurity($cdn)
    {
        if (isset($cdn['enabled'])) {
            unset($cdn['enabled']);
        }
        $cdn = array_map(function ($url) {
            if (false !== strpos($url, '//')) {
                return substr($url, strpos($url, '//') + 2);
            }

            return $url;
        }, array_values($cdn));

        $security = $this->getSettingService()->get('security', []);
        if (is_null($security['safe_iframe_domains'])) {
            $security['safe_iframe_domains'] = [];
        }

        $security['safe_iframe_domains'] = array_merge($security['safe_iframe_domains'], $cdn);
        $security['safe_iframe_domains'] = array_filter(array_unique($security['safe_iframe_domains']));

        $this->getSettingService()->set('security', $security);
        $this->getLogService()->info('system', 'update_settings', '域名白名单添加cdn域名', $cdn);
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
