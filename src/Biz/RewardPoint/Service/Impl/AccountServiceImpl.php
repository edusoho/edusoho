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

        return $this->getAccountDao()->create($account);
    }

    public function updateAccount($id, $fields)
    {
        $fields = $this->filterFields($fields);

        return $this->getAccountDao()->update($id, $fields);
    }

    public function deleteAccount($id)
    {
        return $this->getAccountDao()->delete($id);
    }

    public function deleteAccountByUserId($userId)
    {
        return $this->getAccountDao()->deleteByUserId($userId);
    }

    public function getAccount($id)
    {
        return $this->getAccountDao()->get($id);
    }

    public function getAccountByUserId($id)
    {
        return $this->getAccountDao()->getByUserId($id);
    }

    public function searchAccounts($conditions, $orderBys, $start, $limit)
    {
        return $this->getAccountDao()->search($conditions, $orderBys, $start, $limit);
    }

    public function countAccounts($conditions)
    {
        return $this->getAccountDao()->count($conditions);
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

    private function checkUserExist($userId)
    {
        $user = $this->getUserService()->getUser($userId);

        if (empty($user)) {
            throw $this->createNotFoundException("user {$userId} not exist！");
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
}