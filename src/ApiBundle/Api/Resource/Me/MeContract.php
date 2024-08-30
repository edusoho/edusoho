<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Resource\Contract\ContractDisplayTrait;

class MeContract extends AbstractResource
{
    use ContractDisplayTrait;

    public function search(ApiRequest $request)
    {
        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $conditions = ['userId' => $this->getCurrentUser()->getId()];
        $signedContracts = $this->getContractService()->searchSignedContracts($conditions, ['id' => 'DESC'], $offset, $limit, ['id', 'goodsKey', 'snapshot']);

        return $this->makePagingObject($this->wrap($signedContracts), $this->getContractService()->countSignedContracts($conditions), $offset, $limit);
    }

    private function wrap($signedContracts)
    {
        $contractSnapshots = $this->getContractService()->findContractSnapshotsByIds(array_column(array_column($signedContracts, 'snapshot'), 'contractSnapshotId'), ['id', 'name']);
        $contractSnapshots = array_column($contractSnapshots, null, 'id');
        $wrappedSignedContracts = [];
        foreach ($signedContracts as $signedContract) {
            list($goodsType) = $this->parseGoodsKey($signedContract['goodsKey']);
            $wrappedSignedContracts[] = [
                'id' => $signedContract['id'],
                'name' => $contractSnapshots[$signedContract['snapshot']['contractSnapshotId']]['name'],
                'relatedGoods' => [
                    'type' => $goodsType,
                    'name' => $this->getGoodsName($signedContract['goodsKey']),
                ],
            ];
        }

        return $wrappedSignedContracts;
    }
}
