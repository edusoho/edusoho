<?php

namespace Biz\S2B2C\Service;

interface ResourceSyncService
{
    public function getSync($id);

    public function createSync($sync);

    public function batchCreateSyncs($syncs);

    public function getSyncBySupplierIdAndRemoteResourceIdAndResourceType($supplierId, $remoteResourceId, $resourceType);

    public function findSyncBySupplierIdAndRemoteResourceIdsAndResourceType($supplierId, $remoteResourceIds, $resourceType);

    public function searchSyncs($conditions, $orderBys, $start, $limit, $columns = []);

    public function deleteSync($id);
}
