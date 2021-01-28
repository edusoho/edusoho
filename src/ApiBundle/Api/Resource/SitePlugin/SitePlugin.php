<?php

namespace ApiBundle\Api\Resource\SitePlugin;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;

class SitePlugin extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $code)
    {
        return $this->getAppService()->getAppByCode($code);
    }

    protected function getAppService()
    {
        return $this->service('CloudPlatform:AppService');
    }
}
