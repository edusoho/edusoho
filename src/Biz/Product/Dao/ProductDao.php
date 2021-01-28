<?php

namespace Biz\Product\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ProductDao extends GeneralDaoInterface
{
    public function getByTargetIdAndType($id, $targetType);

    public function findByIds($ids);

    public function findByTargetTypeAndTargetIds($targetType, array $targetIds);
}
