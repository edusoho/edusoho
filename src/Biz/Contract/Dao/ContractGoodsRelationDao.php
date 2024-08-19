<?php

namespace Biz\Contract\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ContractGoodsRelationDao extends GeneralDaoInterface
{
    public function getByGoodsKey($goodsKey);

    public function findByContractIds($contractIds);
}
