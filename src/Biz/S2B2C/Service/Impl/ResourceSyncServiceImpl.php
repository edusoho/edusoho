<?php

namespace Biz\S2B2C\Service\Impl;

use Biz\BaseService;
use Biz\S2B2C\Dao\ResourceSyncDao;

class ResourceSyncServiceImpl extends BaseService
{
    public function getSync($id)
    {
        return $this->getResourceSyncDao()->get($id);
    }

    public function createSync($sync)
    {
        return $this->getResourceSyncDao()->create($sync);
    }

    /**
     * @return ResourceSyncDao
     */
    protected function getResourceSyncDao()
    {
        return $this->createDao('S2B2C:ResourceSyncDao');
    }
}
