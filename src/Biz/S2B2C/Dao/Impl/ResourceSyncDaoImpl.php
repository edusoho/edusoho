<?php

namespace Biz\S2B2C\Dao\Impl;

use Biz\S2B2C\Dao\ResourceSyncDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class ResourceSyncDaoImpl extends AdvancedDaoImpl implements ResourceSyncDao
{
    protected $table = 's2b2c_resource_sync';

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'conditions' => [
                'id = :id',
                'supplierId = :supplierId',
            ],
            'orderbys' => ['id', 'localResourceId', 'remoteResourceId', 'syncTime'],
        ];
    }

    /**
     * @param $remoteResourceId
     * @param $resourceType
     *
     * @return false|mixed[]|null
     */
    public function getByRemoteResourceIdAndResourceType($remoteResourceId, $resourceType)
    {
        if (empty($resourceType) || empty($remoteResourceId)) {
            return null;
        }

        return $this->getByFields(['remoteResourceId' => $remoteResourceId, 'resourceType' => $resourceType]);
    }
}
