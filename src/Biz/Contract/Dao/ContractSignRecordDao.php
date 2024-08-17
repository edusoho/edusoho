<?php

namespace Biz\Contract\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ContractSignRecordDao extends GeneralDaoInterface
{
    public function getByUserIdAndGoodsTypeAndTargetId($userId, $goodsType, $targetId);
}
