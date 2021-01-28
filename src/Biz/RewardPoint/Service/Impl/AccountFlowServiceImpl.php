<?php

namespace Biz\RewardPoint\Service\Impl;

use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\System\Service\LogService;
use Biz\User\Service\UserService;
use Biz\RewardPoint\Dao\AccountFlowDao;
use Biz\RewardPoint\Service\AccountFlowService;
use AppBundle\Common\ArrayToolkit;
use Biz\RewardPoint\Service\AccountService;
use Biz\User\UserException;

class AccountFlowServiceImpl extends BaseService implements AccountFlowService
{
    public function createAccountFlow($flow)
    {
        $flow['sn'] = $this->makeSn();
        $this->validateFields($flow);
        $this->checkUserAccountExist($flow['userId']);
        $flow = $this->filterFields($flow);
        if ('admin_deduction' == $flow['way'] || 'admin_grant' == $flow['way']) {
            $this->getLogService()->info('admin_reward_point_account_flow', $flow['type'], '积分账户', $flow);
        } else {
            $this->getLogService()->info('reward_point_account_flow', $flow['type'], '积分账户', $flow);
        }

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

    public function getInflowByUserIdAndTarget($userId, $targetId, $targetType)
    {
        return $this->getAccountFlowDao()->getInflowByUserIdAndTarget($userId, $targetId, $targetType);
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
                'targetId',
                'targetType',
                'note',
                'way',
            )
        );
    }

    protected function validateFields($fields)
    {
        if (!ArrayToolkit::requireds($fields, array('userId', 'sn', 'type', 'amount', 'operator'))) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }
    }

    protected function checkUserAccountExist($userId)
    {
        $account = $this->getAccountService()->getAccountByUserId($userId);
        $user = $this->getUserService()->getUser($userId);

        if (empty($account) && $user) {
            $this->getAccountService()->createAccount(array('userId' => $userId));
        }

        if (empty($account) && empty($user)) {
            $this->createNewException(UserException::NOTFOUND_USER());
        }
    }

    protected function makeSn()
    {
        return date('YmdHis').rand(10000, 99999);
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return AccountService
     */
    protected function getAccountService()
    {
        return $this->createService('RewardPoint:AccountService');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }

    /**
     * @return AccountFlowDao
     */
    protected function getAccountFlowDao()
    {
        return $this->createDao('RewardPoint:AccountFlowDao');
    }
}
