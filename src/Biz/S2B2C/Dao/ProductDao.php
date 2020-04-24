<?php

namespace Biz\S2B2C\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ProductDao extends GeneralDaoInterface
{
    public function getBySupplierIdAndRemoteProductId($supplierId, $remoteProductId);

    public function findBySupplierIdAndRemoteProductIds($supplierId, $remoteProductIds);
}
