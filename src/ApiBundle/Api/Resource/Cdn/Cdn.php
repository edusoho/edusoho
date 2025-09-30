<?php

namespace ApiBundle\Api\Resource\Cdn;

use ApiBundle\Api\Annotation\Access;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\System\Service\CacheService;
use Biz\System\Service\LogService;

class Cdn extends AbstractResource
{
    /**
     * @return array
     * @Access(roles="ROLE_ADMIN,ROLE_SUPER_ADMIN")
     */
    public function add(ApiRequest $request)
    {
        $data = $request->request->all();
        $cdn = [
            'enabled' => $data['enabled'] ?? '',
            'defaultUrl' => $data['default_url'] ?? '',
            'userUrl' => $data['user_url'] ?? '',
            'contentUrl' => $data['content_url'] ?? '',
        ];

        $this->getSettingService()->set('cdn', $cdn);
        $this->getLogService()->info('system', 'update_settings', 'API设置CDN', $cdn);
        $this->addCdnUrlsToSecurity($cdn);

        return ['code' => 'success', 'msg' => "设置cdn, enabled:{$cdn['enabled']}, defaultUrl:{$cdn['defaultUrl']},userUrl:{$cdn['userUrl']}, contentUrl:{$cdn['contentUrl']}"];
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
        $safeIframeDomains = empty($security['safe_iframe_domains']) ? [] : $security['safe_iframe_domains'];
        $safeIframeDomains = array_filter(array_unique(array_merge($safeIframeDomains, $cdn)));

        $this->getCacheService()->set('safe_iframe_domains', $safeIframeDomains);
    }

    /**
     * @return LogService
     */
    private function getLogService()
    {
        return $this->getBiz()->service('System:LogService');
    }

    /**
     * @return CacheService
     */
    private function getCacheService()
    {
        return $this->getBiz()->service('System:CacheService');
    }
}
