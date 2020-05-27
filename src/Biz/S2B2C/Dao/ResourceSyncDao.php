<?php

namespace Biz\S2B2C\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface ResourceSyncDao extends AdvancedDaoInterface
{
    public function getBySupplierIdAndRemoteResourceIdAndResourceType($supplierId, $remoteResourceId, $resourceType);

    public function findBySupplierIdAndRemoteResourceIdsAndResourceType($supplierId, $remoteResourceIds, $resourceType);
}
