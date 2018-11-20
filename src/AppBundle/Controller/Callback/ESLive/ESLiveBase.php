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
        $setting = $this->setting('storage');
        if (empty($setting['cloud_access_key']) || empty($setting['cloud_secret_key'])) {
            throw new \Exception('Access Denied');
        }

        return new JWTAuth($setting['cloud_access_key'], $setting['cloud_secret_key']);
    }
}
