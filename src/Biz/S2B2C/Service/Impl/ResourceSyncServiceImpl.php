<?php

namespace Biz\S2B2C\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\S2B2C\Dao\ResourceSyncDao;

class ResourceSyncServiceImpl extends BaseService
{
    public function getSync($id)
    {
        return $this->getResourceSyncDao()->get($id);
    }

    /**
     * @param $sync
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function createSync($sync)
    {
        if (!ArrayToolkit::requireds(
            $sync,
            ['supplierId', 'resourceType', 'localResourceId', 'remoteResourceId']
        )) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $sync = ArrayToolkit::parts($sync, [
            'supplierId',
            'resourceType',
            'localResourceId',
            'remoteResourceId',
            'localVersion',
            'remoteVersion',
            'extendedData',
            'syncTime',
        ]);

        return $this->getResourceSyncDao()->create($sync);
    }

    public function searchSyncs($conditions, $orderBys, $start, $limit, $columns = [])
    {
        return $this->getResourceSyncDao()->search($conditions, $orderBys, $start, $limit, $columns);
    }

    /**
     * @return ResourceSyncDao
     */
    protected function getResourceSyncDao()
    {
        return $this->createDao('S2B2C:ResourceSyncDao');
    }
}
