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
     * @param $supplierId
     * @param $remoteResourceId
     * @param $resourceType
     *
     * @return array|null
     */
    public function getBySupplierIdAndRemoteResourceIdAndResourceType($supplierId, $remoteResourceId, $resourceType)
    {
        if (empty($resourceType) || empty($remoteResourceId)) {
            return null;
        }

        return $this->getByFields(['supplierId' => $supplierId, 'remoteResourceId' => $remoteResourceId, 'resourceType' => $resourceType]);
    }

    public function findBySupplierIdAndRemoteResourceIdsAndResourceType($supplierId, $remoteResourceIds, $resourceType)
    {
        if (empty($remoteResourceIds)) {
            return [];
        }
        $marks = str_repeat('?,', count($remoteResourceIds) - 1).'?';
        $sql = "SELECT * FROM {$this->table} WHERE supplierId= ? AND remoteResourceId IN ({$marks}) AND resourceType = ?;";

        return $this->db()->fetchAll($sql, array_merge([$supplierId], array_values($remoteResourceIds), [$resourceType]));
    }
}
