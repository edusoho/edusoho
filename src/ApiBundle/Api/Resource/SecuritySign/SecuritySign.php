<?php

namespace ApiBundle\Api\Resource\SecuritySign;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\System\Service\SettingService;

class SecuritySign extends AbstractResource
{
    /**
     * @return string[]
     * @ApiConf(isRequiredAuth=false)
     */
    public function add(ApiRequest $request)
    {
        return [
            'key' => $this->getKey(),
            'expireIn' => mktime(23, 59, 59, date('m'), date('t'), date('Y')),
        ];
    }

    public function getKey()
    {
        $setting = $this->getSettingService()->get('storage', []);
        $accessKey = !empty($setting['cloud_access_key']) ? $setting['cloud_access_key'] : '';
        $secretKey = !empty($setting['cloud_secret_key']) ? $setting['cloud_secret_key'] : '';
        $beginMonth = mktime(0, 0, 0, date('m'), 1, date('Y'));

        return md5($accessKey.$secretKey.$beginMonth);
    }
}
