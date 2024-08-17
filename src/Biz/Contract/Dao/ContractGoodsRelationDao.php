<?php

namespace Biz\Contract\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ContractGoodsRelationDao extends GeneralDaoInterface
{
    public function getByGoodsTypeAndTargetId($goodsType, $targetId);
}
