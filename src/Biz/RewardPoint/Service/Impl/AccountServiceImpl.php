<?php

namespace Biz\RewardPoint\Service\Impl;

use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\RewardPoint\AccountException;
use Biz\System\Service\LogService;
use Biz\System\Service\SettingService;
use Biz\User\Service\UserService;
use Biz\RewardPoint\Dao\AccountDao;
use Biz\RewardPoint\Service\AccountFlowService;
use Biz\RewardPoint\Service\AccountService;
use AppBundle\Common\ArrayToolkit;
use Biz\User\UserException;

class AccountServiceImpl extends BaseService implements AccountService
{
    public function createAccount($account)
    {
        $this->validateFields($account);
        $account = $this->filterFields($account);
        $this->checkUserExist($account['userId']);
        $this->checkUserAccountOpened($account['userId']);

        $account = $this->getAccountDao()->create($account);
        $this->getLogService()->info('reward_point_account', 'create', '积分账户', $account);

        return $account;
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

        $result = $this->getAccountDao()->delete($id);
        $this->getLogService()->info('reward_point_account', 'delete', '积分账户', array('id' => $id, 'result' => $result));

        return $result;
    }

    public function deleteAccountByUserId($userId)
    {
        $this->checkAccountExistByUserId($userId);

        $result = $this->getAccountDao()->deleteByUserId($userId);
        $this->getLogService()->info('reward_point_account', 'delete', '积分账户', array('userId' => $userId, 'result' => $result));

        return $result;
    }

    public function getAccount($id)
    {
        return $this->getAccountDao()->get($id);
    }

    public function getAccountByUserId($userId, $potions = array())
    {
        return $this->getAccountDao()->getByUserId($userId, $potions);
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
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }

        $balanceFields = array(
            'balance' => abs($value),
            'inflowAmount' => abs($value),
        );

        return $this->getAccountDao()->wave(array($id), $balanceFields);
    }

    public function waveDownBalance($id, $value)
    {
        $this->checkAccountExist($id);

        if (!is_numeric($value)) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }

        $balanceFields = array(
            'balance' => -abs($value),
            'outflowAmount' => abs($value),
        );

        return $this->getAccountDao()->wave(array($id), $balanceFields);
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
        try {
            $this->beginTransaction();
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

            $account = $this->getAccountByUserId($id);
            $this->commit();

            return $account;
        } catch (\Exception $exception) {
            $this->rollback();
            throw $exception;
        }
    }

    public function deductionRewardPoint($id, $profile)
    {
        $operator = $this->getCurrentUser();
        $account = $this->getAccountByUserId($id);
        $flow = array(
            'userId' => $id,
            'type' => 'outflow',
            'amount' => $profile['amount'],
            'operator' => $operator['id'],
            'way' => 'admin_deduction',
            'note' => $profile['note'],
        );
        try {
            $this->beginTransaction();
            if (empty($account)) {
                $account = array(
                    'userId' => $id,
                    'balance' => $profile['amount'],
                );
                $this->createAccount($account);
            } else {
                if ($flow['amount'] > $account['balance']) {
                    $this->createNewException(AccountException::BALANCE_INSUFFICIENT());
                }
                $this->waveDownBalance($account['id'], $flow['amount']);
            }
            $this->getAccountFlowService()->createAccountFlow($flow);

            $account = $this->getAccountByUserId($id);
            $this->commit();

            return $account;
        } catch (\Exception $exception) {
            $this->rollback();
            throw $exception;
        }
    }

    public function hasRewardPointPermission()
    {
        $settings = $this->getSettingService()->get('reward_point', array());
        if (isset($settings['enable']) && 1 == $settings['enable']) {
            return true;
        } else {
            return false;
        }
    }

    protected function filterFields($fields)
    {
        return ArrayToolkit::parts($fields, array('userId', 'balance'));
    }

    protected function validateFields($fields)
    {
        if (!ArrayToolkit::requireds($fields, array('userId'))) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }
    }

    protected function checkUserExist($userId)
    {
        $user = $this->getUserService()->getUser($userId);

        if (empty($user)) {
            $this->createNewException(UserException::NOTFOUND_USER());
        }
    }

    protected function checkAccountExist($id)
    {
        $account = $this->getAccount($id);

        if (empty($account)) {
            $this->createNewException(AccountException::NOTFOUND_ACCOUNT());
        }

        return $account;
    }

    protected function checkUserAccountOpened($userId)
    {
        $account = $this->getAccountByUserId($userId);

        if (!empty($account)) {
            $this->createNewException(AccountException::ALREADY_OPEN());
        }
    }

    protected function checkAccountExistByUserId($userId)
    {
        $account = $this->getAccountByUserId($userId);

        if (empty($account)) {
            $this->createNewException(AccountException::NOTFOUND_ACCOUNT());
        }
    }

    protected function checkUserCorrect($originUserId, $newUserId)
    {
        if ($originUserId != $newUserId) {
            $this->createNewException(AccountException::USERID_INVALID());
        }
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return AccountDao
     */
    protected function getAccountDao()
    {
        return $this->createDao('RewardPoint:AccountDao');
    }

    /**
     * @return AccountFlowService
     */
    protected function getAccountFlowService()
    {
        return $this->createService('RewardPoint:AccountFlowService');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
