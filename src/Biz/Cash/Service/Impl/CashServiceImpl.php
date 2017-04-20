<?php

namespace Biz\Cash\Service\Impl;

use Biz\BaseService;
use Biz\Cash\Dao\CashFlowDao;
use Biz\Cash\Service\CashAccountService;
use Biz\Cash\Service\CashService;
use Biz\System\Service\SettingService;
use Biz\User\Service\NotificationService;
use AppBundle\Common\ArrayToolkit;

class CashServiceImpl extends BaseService implements CashService
{
    public function searchFlows($conditions, $orderBy, $start, $limit)
    {
        return $this->getFlowDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function searchFlowsCount($conditions)
    {
        return $this->getFlowDao()->count($conditions);
    }

    public function analysisAmount($conditions)
    {
        return $this->getFlowDao()->analysisAmount($conditions);
    }

    public function outflowByCoin($outflow)
    {
        if (!ArrayToolkit::requireds($outflow, array(
            'userId', 'amount', 'name', 'orderSn', 'category', 'note',
        ))
        ) {
            throw $this->createInvalidArgumentException('参数缺失');
        }

        if (!is_numeric($outflow['amount']) || $outflow['amount'] <= 0) {
            throw $this->createInvalidArgumentException('金额必须为数字，并且不能小于0');
        }

        $account = $this->getCashAccountService()->getAccountByUserId($outflow['userId'], true);

        if (empty($account)) {
            $this->getCashAccountService()->createAccount($outflow['userId']);
            $account = $this->getCashAccountService()->getAccountByUserId($outflow['userId'], true);
        }
        if ((round($account['cash'] * 100) / 100 - round($outflow['amount'] * 100) / 100) < 0) {
            return false;
        }

        $outflow['cashType'] = 'Coin';
        $outflow['type'] = 'outflow';
        $outflow['sn'] = $this->makeSn();
        $outflow['createdTime'] = time();
        $outflow['cash'] = $account['cash'] - $outflow['amount'];

        $outflow = $this->getFlowDao()->create($outflow);

        $this->getCashAccountService()->waveDownCashField($account['id'], $outflow['amount']);

        return $outflow;
    }

    public function inflowByCoin($inflow)
    {
        if (!ArrayToolkit::requireds($inflow, array(
            'userId', 'amount', 'name', 'orderSn', 'category', 'note',
        ))
        ) {
            throw $this->createInvalidArgumentException('参数缺失');
        }

        if (!is_numeric($inflow['amount']) || $inflow['amount'] <= 0) {
            throw $this->createInvalidArgumentException('金额必须为数字，并且不能小于0');
        }

        $account = $this->getCashAccountService()->getAccountByUserId($inflow['userId']);
        if (empty($account)) {
            $this->getCashAccountService()->createAccount($inflow['userId']);
            $account = $this->getCashAccountService()->getAccountByUserId($inflow['userId'], true);
        }

        $inflow['cashType'] = 'Coin';
        $inflow['type'] = 'inflow';
        $inflow['sn'] = $this->makeSn();
        $inflow['createdTime'] = time();
        $inflow['cash'] = $account['cash'] + $inflow['amount'];

        $inflow = $this->getFlowDao()->create($inflow);

        $this->getCashAccountService()->waveCashField($account['id'], $inflow['amount']);

        return $inflow;
    }

    public function inflowByRmb($inflow)
    {
        if (!ArrayToolkit::requireds($inflow, array(
            'userId', 'amount', 'name', 'orderSn', 'category', 'note',
        ))
        ) {
            throw $this->createInvalidArgumentException('参数缺失');
        }

        if (!is_numeric($inflow['amount']) || $inflow['amount'] <= 0) {
            throw $this->createInvalidArgumentException('金额必须为数字，并且不能小于0');
        }

        $inflow['cashType'] = 'RMB';
        $inflow['type'] = 'inflow';
        $inflow['sn'] = $this->makeSn();
        $inflow['createdTime'] = time();

        $inflow = $this->getFlowDao()->create($inflow);

        return $inflow;
    }

    public function outflowByRmb($outflow)
    {
        if (!ArrayToolkit::requireds($outflow, array(
            'userId', 'amount', 'name', 'orderSn', 'category', 'note',
        ))
        ) {
            throw $this->createInvalidArgumentException('参数缺失');
        }

        if (!is_numeric($outflow['amount']) || $outflow['amount'] <= 0) {
            throw $this->createInvalidArgumentException('金额必须为数字，并且不能小于0');
        }

        $outflow['cashType'] = 'RMB';
        $outflow['type'] = 'outflow';
        $outflow['sn'] = $this->makeSn();
        $outflow['createdTime'] = time();

        $outflow = $this->getFlowDao()->create($outflow);

        return $outflow;
    }

    public function findUserIdsByFlows($type, $createdTime, $orderBy, $start, $limit)
    {
        return $this->getFlowDao()->findUserIdsByFlows($type, $createdTime, $orderBy, $start, $limit);
    }

    public function findUserIdsByFlowsCount($type, $createdTime)
    {
        return $this->getFlowDao()->countByTypeAndGTECreatedTime($type, $createdTime);
    }

    public function changeRmbToCoin($rmbFlow)
    {
        $outflow = $this->outflowByRmb($rmbFlow);

        $coinSetting = $this->getSettingService()->get('coin');

        $coinRate = 1;
        if (!empty($coinSetting) && array_key_exists('cash_rate', $coinSetting)) {
            $coinRate = $coinSetting['cash_rate'];
        }

        $amount = $outflow['amount'] * $coinRate;
        $rmbInFlow = $this->getFlowDao()->getBySn($outflow['parentSn']);
        $inflow = array(
            'userId' => $outflow['userId'],
            'amount' => $amount,
            'name' => '充值',
            'orderSn' => $outflow['orderSn'],
            'category' => 'change',
            'note' => '',
            'parentSn' => $outflow['sn'],
            'payment' => $rmbInFlow['payment'],
        );

        $inflow['cashType'] = 'Coin';
        $inflow['type'] = 'inflow';
        $inflow['sn'] = $this->makeSn();
        $inflow['createdTime'] = time();

        $account = $this->getCashAccountService()->getAccountByUserId($inflow['userId'], true);
        if (empty($account)) {
            $this->getCashAccountService()->createAccount($inflow['userId']);
            $account = $this->getCashAccountService()->getAccountByUserId($inflow['userId'], true);
        }

        $inflow['cash'] = $account['cash'] + $inflow['amount'];

        $inflow = $this->getFlowDao()->create($inflow);

        $this->getCashAccountService()->waveCashField($account['id'], $inflow['amount']);

        return $inflow;
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
     * @return CashFlowDao
     */
    protected function getFlowDao()
    {
        return $this->createDao('Cash:CashFlowDao');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return CashAccountService
     */
    protected function getCashAccountService()
    {
        return $this->createService('Cash:CashAccountService');
    }
}
