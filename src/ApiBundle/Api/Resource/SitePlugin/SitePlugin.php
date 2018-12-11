<?php

namespace ApiBundle\Api\Resource\SitePlugin;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Annotation\Access;
use ApiBundle\Api\Resource\AbstractResource;

class SitePlugin extends AbstractResource
{
    /**
     * @Access(roles="ROLE_SUPER_ADMIN")
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
