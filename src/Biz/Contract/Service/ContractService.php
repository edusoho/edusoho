<?php

namespace Biz\Contract\Service;

interface ContractService
{
    public function createContract(array $params);

    public function getContract($id);

    public function signContract($id, $sign);

    public function getBindContractByGoodsTypeAndTargetId($goodsType, $targetId);

    public function getSignRecordByUserIdAndGoodsTypeAndTargetId($userId, $goodsType, $targetId);
}
