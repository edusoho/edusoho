<?php

namespace Biz\Contract\Service;

interface ContractService
{
    public function countContracts(array $conditions);

    public function searchContracts(array $conditions, array $orderBys, $start, $limit, array $columns = []);

    public function createContract(array $params);

    public function getContract($id);

    public function updateContract($id, array $params);

    public function deleteContract($id);

    public function generateContractCode();

    public function signContract($id, $sign);

    public function countSignedContracts(array $conditions);

    public function searchSignedContracts(array $conditions, array $orderBys, $start, $limit, array $columns = []);

    public function getSignedContract($id);

    public function getRelatedContractByGoodsKey($goodsKey);

    public function relateContract($id, $goodsKey, $forceSign);

    public function unRelateContract($goodsKey);

    public function findContractGoodsRelationsByContractIds($contractIds);

    public function getContractGoodsRelationByContractId($contractId);

    public function getSignRecordByUserIdAndGoodsKey($userId, $goodsKey);

    public function findContractSnapshotsByIds($ids, $columns = []);

    public function getContractDetail($contract);
}
