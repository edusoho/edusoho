<?php

namespace MarketingMallBundle\Api\Resource\SyncList;

use ApiBundle\Api\ApiRequest;
use MarketingMallBundle\Api\Resource\BaseResource;
use MarketingMallBundle\Biz\SyncList\Service\SyncListService;

class SyncList extends BaseResource
{
    public function search(ApiRequest  $request)
    {
        $request = $request->query;
        $cursorAddress = $request->get("cursorAddress");
        $cursorType = $request->get("cursorType");
        return $this->getSyncListService()->getSyncList($cursorAddress, $cursorType);
    }

    /**
     * @return SyncListService
     */
    protected function getSyncListService()
    {
        return $this->service('MarketingMallBundle:SyncList:SyncListService');
    }
}

