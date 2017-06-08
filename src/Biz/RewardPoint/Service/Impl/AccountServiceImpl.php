<?php

namespace Biz\RewardPoint\Service\Impl;

use Biz\BaseService;
use Biz\RewardPoint\Service\AccountService;
use AppBundle\Common\ArrayToolkit;

class AccountServiceImpl extends BaseService implements AccountService
{
    public function createAccount($account)
    {
        $this->validateFields($account);
        $account = $this->filterFields($account);
        $this->checkUserExist($account['userId']);
        $this->checkUserAccountOpened($account['userId']);

        return $this->getAccountDao()->create($account);
    }

    public function updateAccount($id, $fields)
    {
        $account = $this->checkAccountExist($id);
        if (!empty($account) && !empty($fields['userId'])) {
            $this->checkUserExist($fields['userId']);
            $this->checkUserCorrect($account['userId'], $fields['userId']);
        }
        $fields = $this->filterFields($fields);

        return $this->getAccountDao()->update($id, $fields);
    }

    public function deleteAccount($id)
    {
        $this->checkAccountExist($id);

        return $this->getAccountDao()->delete($id);
    }

    public function deleteAccountByUserId($userId)
    {
        $this->checkAccountExistByUserId($userId);

        return $this->getAccountDao()->deleteByUserId($userId);
    }

    public function getAccount($id)
    {
        return $this->getAccountDao()->get($id);
    }

    public function getAccountByUserId($userId)
    {
        return $this->getAccountDao()->getByUserId($userId);
    }

    public function searchAccounts($conditions, $orderBys, $start, $limit)
    {
        return $this->getAccountDao()->search($conditions, $orderBys, $start, $limit);
    }

    public function countAccounts($conditions)
    {
        return $this->getAccountDao()->count($conditions);
    }

    public function waveBalance($id, $value)
    {
        $this->checkAccountExist($id);

        if (!is_numeric($value)) {
            throw $this->createInvalidArgumentException('The value must be an integer!');
        }

        return $this->getAccountDao()->waveBalance($id, $value);
    }

    public function waveDownBalance($id, $value)
    {
        $this->checkAccountExist($id);

        if (!is_numeric($value)) {
            throw $this->createInvalidArgumentException('The value must be an integer!');
        }

        return $this->getAccountDao()->waveDownBalance($id, $value);
    }

    public function grantRewardPoint($id, $profile)
    {
        $operator = $this->getCurrentUser();
        $account = $this->getAccountByUserId($id);
        $flow = array(
            'userId' => $id,
            'type' => 'inflow',
            'amount' => $profile['amount'],
            'operator' => $operator['id'],
            'way' => 'admin_grant',
            'note' => $profile['note'],
        );
        if (empty($account)) {
            $account = array(
                'userId' => $id,
                'balance' => $profile['amount'],
            );
            $this->createAccount($account);
        } else {
            $this->waveBalance($account['id'], $flow['amount']);
        }
        $this->getAccountFlowService()->createAccountFlow($flow);

        return $this->getAccountByUserId($id);
    }

    public function detailRewardPoint($id, $profile)
    {
        $operator = $this->getCurrentUser();
        $account = $this->getAccountByUserId($id);
        $flow = array(
            'userId' => $id,
            'type' => 'outflow',
            'amount' => $profile['amount'],
            'operator' => $operator['id'],
            'way' => 'admin_detail',
            'note' => $profile['note'],
        );
        if (empty($account)) {
            $account = array(
                'userId' => $id,
                'balance' => $profile['amount'],
            );
            $this->createAccount($account);
        } else {
            if ($flow['amount'] > $account['balance']) {
                throw $this->createInvalidArgumentException('Insufficient Balance');
            }
            $this->waveDownBalance($account['id'], $flow['amount']);
        }
        $this->getAccountFlowService()->createAccountFlow($flow);

        return $this->getAccountByUserId($id);
    }

    protected function filterFields($fields)
    {
        return ArrayToolkit::parts($fields, array('userId', 'balance'));
    }

    protected function validateFields($fields)
    {
        if (!ArrayToolkit::requireds($fields, array('userId'))) {
            throw $this->createInvalidArgumentException('Lack of required fields');
        }
    }

    protected function checkUserExist($userId)
    {
        $user = $this->getUserService()->getUser($userId);

        if (empty($user)) {
            throw $this->createNotFoundException("user {$userId} not exist！");
        }
    }

    protected function checkAccountExist($id)
    {
        $account = $this->getAccount($id);

        if (empty($account)) {
            throw $this->createNotFoundException("account {$id} not exist");
        }

        return $account;
    }

    protected function checkUserAccountOpened($userId)
    {
        $account = $this->getAccountByUserId($userId);

        if (!empty($account)) {
            throw $this->createInvalidArgumentException("user{$userId}'s account have been opened");
        }
    }

    protected function checkAccountExistByUserId($userId)
    {
        $account = $this->getAccountByUserId($userId);

        if (empty($account)) {
            throw $this->createNotFoundException("user'{$userId} account not exist");
        }
    }

    protected function checkUserCorrect($originUserId, $newUserId)
    {
        if ($originUserId != $newUserId) {
            throw $this->createInvalidArgumentException('Param Invalid: userId');
        }
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    protected function getAccountDao()
    {
        return $this->createDao('RewardPoint:AccountDao');
    }

    protected function getAccountFlowService()
    {
        return $this->createService('RewardPoint:AccountFlowService');
    }
}
