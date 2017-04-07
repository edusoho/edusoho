<?php

namespace Biz\Cash\Service\Impl;

use Biz\BaseService;
use Biz\Cash\Dao\CashAccountDao;
use Biz\Cash\Dao\CashChangeDao;
use Biz\Cash\Dao\CashFlowDao;
use Biz\Cash\Service\CashAccountService;
use Biz\System\Service\SettingService;
use Biz\User\Service\NotificationService;

class CashAccountServiceImpl extends BaseService implements CashAccountService
{
    public function createAccount($userId)
    {
        $fields = array('userId' => $userId, 'cash' => 0);

        return $this->getAccountDao()->create($fields);
    }

    public function getAccountByUserId($userId, $lock = false)
    {
        return $this->getAccountDao()->getByUserId($userId, array('lock' => $lock));
    }

    public function findAccountsByUserIds($userIds)
    {
        return $this->getAccountDao()->findByUserIds($userIds);
    }

    public function getAccount($id)
    {
        return $this->getAccountDao()->get($id);
    }

    public function getChangeByUserId($userId)
    {
        return $this->getChangeDao()->getByUserId($userId);
    }

    public function addChange($userId)
    {
        $fields = array(
            'userId' => $userId,
            'amount' => 0,
        );

        return $this->getChangeDao()->create($fields);
    }

    public function changeCoin($amount, $coinAmount, $userId)
    {
        $coinSetting = $this->getSettingService()->get('coin', array());

        try {
            $this->beginTransaction();

            $account = $this->getAccountDao()->getByUserId($userId, array('lock' => true));
            if (empty($account)) {
                throw $this->createNotFoundException(sprintf('Account #%s is not exist.', $userId));
            }

            $inflow = array(
                'userId' => $userId,
                'sn' => $this->makeSn(),
                'type' => 'inflow',
                'amount' => $coinAmount,
                'name' => '兑换'.$coinAmount.$coinSetting['coin_name'],
                'category' => 'exchange',
                'orderSn' => 'E'.$this->makeSn(),
                'createdTime' => time(),
            );

            $inflow = $this->getFlowDao()->create($inflow);

            $this->getAccountDao()->waveCashField($account['id'], $coinAmount);

            $change = $this->getChangeDao()->getByUserId($userId, array('lock' => true));

            $this->getChangeDao()->waveCashField($change['id'], $amount);

            $message = array(
                'value' => $coinAmount,
                'type' => 'changing',
            );
            $this->getNotificationService()->notify($userId, 'cash-account', $message);

            $this->commit();

            return $inflow;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function searchAccount($conditions, $orderBy, $start, $limit)
    {
        return $this->getAccountDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function searchAccountCount($conditions)
    {
        return $this->getAccountDao()->count($conditions);
    }

    public function waveCashField($id, $value)
    {
        if (!is_numeric($value)) {
            throw $this->createInvalidArgumentException('充值金额必须为整数!');
        }
        $coinSetting = $this->getSettingService()->get('coin', array());
        $coinSetting['coin_name'] = isset($coinSetting['coin_name']) ? $coinSetting['coin_name'] : '虚拟币';

        $account = $this->getAccount($id);
        $message = array(
            'value' => $value,
            'type' => 'changeOk',
        );
        $this->getNotificationService()->notify($account['userId'], 'cash-account', $message);

        return $this->getAccountDao()->waveCashField($id, $value);
    }

    public function waveDownCashField($id, $value)
    {
        if (!is_numeric($value)) {
            throw $this->createInvalidArgumentException('充值金额必须为整数!');
        }

        $coinSetting = $this->getSettingService()->get('coin', array());
        $coinSetting['coin_name'] = isset($coinSetting['coin_name']) ? $coinSetting['coin_name'] : '虚拟币';
        $account = $this->getAccountDao()->get($id);

        $message = array(
            'value' => $value,
            'type' => 'deduct',
        );
        $this->getNotificationService()->notify($account['userId'], 'cash-account', $message);

        return $this->getAccountDao()->waveDownCashField($id, $value);
    }

    public function reward($amount, $name, $userId, $type = null)
    {
        try {
            $this->beginTransaction();
            $account = $this->getAccountDao()->getByUserId($userId, array('lock' => true));

            if (empty($account)) {
                $account = $this->createAccount($userId);
            }

            if ($type == 'cut') {
                $inflow = array(
                    'userId' => $userId,
                    'sn' => $this->makeSn(),
                    'type' => 'outflow',
                    'amount' => $amount,
                    'name' => $name,
                    'category' => 'exchange',
                    'orderSn' => 'R'.$this->makeSn(),
                    'createdTime' => time(),
                );

                $inflow = $this->getFlowDao()->create($inflow);

                $this->getAccountDao()->waveDownCashField($account['id'], $amount);
            } else {
                $inflow = array(
                    'userId' => $userId,
                    'sn' => $this->makeSn(),
                    'type' => 'inflow',
                    'amount' => $amount,
                    'name' => $name,
                    'category' => 'exchange',
                    'orderSn' => 'R'.$this->makeSn(),
                    'createdTime' => time(),
                );

                $inflow = $this->getFlowDao()->create($inflow);

                $this->getAccountDao()->waveCashField($account['id'], $amount);
            }

            $this->commit();

            return $inflow;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    protected function makeSn()
    {
        return date('YmdHis').rand(10000, 99999);
    }

    /**
     * @return NotificationService
     */
    protected function getNotificationService()
    {
        return $this->createService('User:NotificationService');
    }

    /**
     * @return CashAccountDao
     */
    protected function getAccountDao()
    {
        return $this->createDao('Cash:CashAccountDao');
    }

    /**
     * @return CashChangeDao
     */
    protected function getChangeDao()
    {
        return $this->createDao('Cash:CashChangeDao');
    }

    /**
     * @return CashFlowDao
     */
    protected function getFlowDao()
    {
        return $this->createDao('Cash:CashFlowDao');
    }
}
