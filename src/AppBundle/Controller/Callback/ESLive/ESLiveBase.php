<?php

namespace AppBundle\Controller\Callback\ESLive;

use AppBundle\Common\JWTAuth;
use AppBundle\Controller\BaseController;

class ESLiveBase extends BaseController
{
    /**
     * @return JWTAuth
     *
     * @throws \Exception
     */
    protected function getJWTAuth()
    {
        $setting = $this->getSettingService()->get('storage', array());
        $accessKey = $setting['cloud_access_key'] ?: '';
        $secretKey = $setting['cloud_secret_key'] ?: '';

        return new JWTAuth($accessKey, $secretKey);
    }
}
