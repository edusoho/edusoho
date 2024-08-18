<?php

namespace Biz\Contract\Service;

interface ContractService
{
    public function countContracts(array $conditions);

    public function searchContracts(array $conditions, array $orderBys, $start, $limit, array $columns = []);

    public function createContract(array $params);

    public function getContract($id);

    public function updateContract($id, array $params);

    public function signContract($id, $sign);

    public function countSignedContracts(array $conditions);

    public function searchSignedContracts(array $conditions, array $orderBys, $start, $limit, array $columns = []);

    public function getBindContractByGoodsKey($goodsKey);

    public function getSignRecordByUserIdAndGoodsKey($userId, $goodsKey);

    public function findContractSnapshotsByIds($ids, $columns = []);
}
