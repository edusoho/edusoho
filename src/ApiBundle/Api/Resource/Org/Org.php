<?php

namespace ApiBundle\Api\Resource\Org;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Org\Service\OrgService;

class Org extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        $orgCode = $this->getCurrentUser()->isSuperAdmin() ? null : $this->getCurrentUser()->getOrgCode();

        return $this->getOrgService()->findOrgsByPrefixOrgCode($orgCode);
    }

    /**
     * @return OrgService
     */
    private function getOrgService()
    {
        return $this->service('Org:OrgService');
    }
}
