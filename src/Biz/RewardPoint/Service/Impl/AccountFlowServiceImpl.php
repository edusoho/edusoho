<?php

namespace Biz\RewardPoint\Service\Impl;

use Biz\BaseService;
use Biz\RewardPoint\Service\AccountFlowService;
use AppBundle\Common\ArrayToolkit;

class AccountFlowServiceImpl extends BaseService implements AccountFlowService
{
    public function createAccountFlow($flow)
    {
        $this->validateFields($flow);
        $flow = $this->filterFields($flow);

        return $this->getAccountFlowDao()->create($flow);
    }

    public function updateAccountFlow($id, $fields)
    {
        $fields = $this->filterFields($fields);

        return $this->getAccountFlowDao()->update($id, $fields);
    }

    public function deleteAccountFlow($id)
    {
        return $this->getAccountFlowDao()->delete($id);
    }

    public function getAccountFlow($id)
    {
        return $this->getAccountFlowDao()->get($id);
    }

    public function searchAccountFlows(array $conditions, $orderBys, $start, $limit)
    {
        return $this->getAccountFlowDao()->search($conditions, $orderBys, $start, $limit);
    }

    public function countAccountFlows(array $conditions)
    {
        return $this->getAccountFlowDao()->count($conditions);
    }

    protected function filterFields($fields)
    {
        return ArrayToolkit::column(
            $fields,
            array(
                'userId',
                'sn',
                'type',
                'amount',
                'name',
                'operator',
            )
        );
    }

    protected function validateFields($fields)
    {
        if (!ArrayToolkit::requireds($fields, array('userId', 'sn', 'type', 'amount', 'operator'))) {
            throw $this->createInvalidArgumentException('Lack of required fields');
        }
    }

    protected function getAccountFlowDao()
    {
        return $this->createDao('RewardPoint:AccountFlowDao');
    }
}