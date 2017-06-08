<?php

namespace Biz\RewardPoint\Service\Impl;

use Biz\BaseService;
use Biz\RewardPoint\Service\AccountFlowService;
use AppBundle\Common\ArrayToolkit;

class AccountFlowServiceImpl extends BaseService implements AccountFlowService
{
    public function createAccountFlow($flow)
    {
        $flow['sn'] = $this->makeSn();
        $this->validateFields($flow);
        $this->checkUserAccountExist($flow['userId']);
        $flow = $this->filterFields($flow);
        $this->getLogService()->info('accountFlow', 'create', '积分账户', $flow);

        return $this->getAccountFlowDao()->create($flow);
    }

    public function updateAccountFlow($id, $fields)
    {
        if (!empty($fields['userId'])) {
            $this->checkUserAccountExist($fields['userId']);
        }
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

    public function sumAccountOutFlowByUserId($userId)
    {
        return $this->getAccountFlowDao()->sumAccountOutFlowByUserId($userId);
    }

    public function sumInflowByUserIdAndWayAndTime($userId, $way, $startTime, $endTime)
    {
        return $this->getAccountFlowDao()->sumInflowByUserIdAndWayAndTime($userId, $way, $startTime, $endTime);
    }

    public function sumInflowByUserId($userId)
    {
        return $this->getAccountFlowDao()->sumInflowByUserId($userId);
    }

    protected function filterFields($fields)
    {
        return ArrayToolkit::parts(
            $fields,
            array(
                'userId',
                'sn',
                'type',
                'amount',
                'name',
                'operator',
                'note',
                'way',
            )
        );
    }

    protected function validateFields($fields)
    {
        if (!ArrayToolkit::requireds($fields, array('userId', 'sn', 'type', 'amount', 'operator'))) {
            throw $this->createInvalidArgumentException('Lack of required fields');
        }
    }

    protected function checkUserAccountExist($userId)
    {
        $account = $this->getAccountService()->getAccountByUserId($userId);

        if (empty($account)) {
            throw $this->createNotFoundException("user{$userId}'s account have been opened");
        }
    }

    public function grantRewardPoint($profile, $account, $id)
    {
        $operator = $this->getCurrentUser();
        $flow = array(
            'userId' => $id,
            'type' => 'inflow',
            'amount' => $profile['amount'],
            'operator' => $operator['id'],
            'way' => '发放积分',
            'note' => $profile['note'],
        );
        if (empty($account)) {
            $this->getAccountService()->createAccount($flow);
        } else {
            $this->getAccountService()->waveBalance($account['id'], $flow['amount']);
        }

        return $this->createAccountFlow($flow);
    }

    public function detailRewardPoint($profile, $account, $id)
    {
        $operator = $this->getCurrentUser();
        $flow = array(
            'userId' => $id,
            'type' => 'outflow',
            'amount' => $profile['amount'],
            'operator' => $operator['id'],
            'way' => '扣减积分',
            'note' => $profile['note'],
        );
        if (empty($account)) {
            $this->getAccountService()->createAccount($flow);
        } else {
            if ($flow['amount'] > $account['balance']) {
                throw $this->createInvalidArgumentException('Insufficient Balance');
            }
            $this->getAccountService()->waveDownBalance($account['id'], $flow['amount']);
        }

        return $this->createAccountFlow($flow);
    }

    protected function makeSn()
    {
        return date('YmdHis').rand(10000, 99999);
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    protected function getAccountService()
    {
        return $this->createService('RewardPoint:AccountService');
    }

    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }

    protected function getAccountFlowDao()
    {
        return $this->createDao('RewardPoint:AccountFlowDao');
    }
}
