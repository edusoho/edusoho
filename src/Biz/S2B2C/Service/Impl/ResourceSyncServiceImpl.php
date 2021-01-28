<?php

namespace Biz\S2B2C\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\S2B2C\Dao\ResourceSyncDao;
use Biz\S2B2C\Service\ResourceSyncService;

class ResourceSyncServiceImpl extends BaseService implements ResourceSyncService
{
    public function getSync($id)
    {
        return $this->getResourceSyncDao()->get($id);
    }

    public function getSyncBySupplierIdAndRemoteResourceIdAndResourceType($supplierId, $remoteResourceId, $resourceType)
    {
        return $this->getResourceSyncDao()->getBySupplierIdAndRemoteResourceIdAndResourceType($supplierId, $remoteResourceId, $resourceType);
    }

    public function findSyncBySupplierIdAndRemoteResourceIdsAndResourceType($supplierId, $remoteResourceIds, $resourceType)
    {
        return $this->getResourceSyncDao()->findBySupplierIdAndRemoteResourceIdsAndResourceType($supplierId, $remoteResourceIds, $resourceType);
    }

    /**
     * @param $sync
     *
     * @return mixed
     */
    public function createSync($sync)
    {
        $sync = $this->validateSync($sync);

        return $this->getResourceSyncDao()->create($sync);
    }

    /**
     * @param $syncs
     *
     * @return array
     */
    public function batchCreateSyncs($syncs)
    {
        if (empty($syncs)) {
            return [];
        }
        foreach ($syncs as &$sync) {
            $sync = $this->validateSync($sync);
        }

        $this->getResourceSyncDao()->batchCreate($syncs);
    }

    public function searchSyncs($conditions, $orderBys, $start, $limit, $columns = [])
    {
        return $this->getResourceSyncDao()->search($conditions, $orderBys, $start, $limit, $columns);
    }

    protected function validateSync($sync)
    {
        if (!ArrayToolkit::requireds(
            $sync,
            ['supplierId', 'resourceType', 'localResourceId', 'remoteResourceId']
        )) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        return ArrayToolkit::parts($sync, [
            'supplierId',
            'resourceType',
            'localResourceId',
            'remoteResourceId',
            'localVersion',
            'remoteVersion',
            'extendedData',
            'syncTime',
        ]);
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    public function deleteSync($id)
    {
        return $this->getResourceSyncDao()->delete($id);
    }

    /**
     * @return ResourceSyncDao
     */
    protected function getResourceSyncDao()
    {
        return $this->createDao('S2B2C:ResourceSyncDao');
    }
}
