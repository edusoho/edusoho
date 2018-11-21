<?php

namespace AppBundle\Controller\Callback\ESLive;

use AppBundle\Common\JWTAuth;
use AppBundle\Controller\BaseController;

class ESLiveBase extends BaseController
{
    protected function getJWTAuth()
    {
        $setting = $this->setting('storage', array());
        $accessKey = !empty($setting['cloud_access_key']) ? $setting['cloud_access_key'] : '';
        $secretKey = !empty($setting['cloud_secret_key']) ? $setting['cloud_secret_key'] : '';

        return new JWTAuth($accessKey, $secretKey);
    }
}
