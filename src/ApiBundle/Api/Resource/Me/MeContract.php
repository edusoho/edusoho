<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Resource\SignedContract\SignedContractWrapTrait;

class MeContract extends AbstractResource
{
    use SignedContractWrapTrait;

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
            list($goodsType, $targetId) = explode('_', $signedContract['goodsKey']);
            $wrappedSignedContracts[] = [
                'id' => $signedContract['id'],
                'name' => $contractSnapshots[$signedContract['snapshot']['contractSnapshotId']]['name'],
                'relatedGoods' => [
                    'type' => $goodsType,
                    'name' => $this->getGoodsName($goodsType, $targetId),
                ],
            ];
        }

        return $wrappedSignedContracts;
    }
}
