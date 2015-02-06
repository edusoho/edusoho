<?php
namespace Topxia\Service\Cash\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Cash\CashService;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Util\EasyValidator;

class CashServiceImpl extends BaseService implements CashService
{
    public function searchFlows($conditions, $orderBy, $start, $limit)
    {
        return $this->getFlowDao()->searchFlows($conditions, $orderBy, $start, $limit);
    }

    public function searchFlowsCount($conditions)
    {
        return $this->getFlowDao()->searchFlowsCount($conditions);
    }
    public function analysisAmount($conditions)
    {
        return $this->getFlowDao()->analysisAmount($conditions);
    }
    public function outflowByCoin($outflow)
    {
        if(!ArrayToolkit::requireds($outflow, array(
            'userId', 'amount', 'name', 'orderSn', 'category', 'note'
        ))){
            throw $this->createServiceException('参数缺失');
        }

        if(!is_numeric($outflow["amount"]) || $outflow["amount"] <= 0) {
            throw $this->createServiceException('金额必须为数字，并且不能小于0');
        }

        $account = $this->getCashAccountService()->getAccountByUserId($outflow["userId"], true);
        if(empty($account)){
            $account = $this->getCashAccountService()->createAccount($outflow["userId"]);
            $account = $this->getCashAccountService()->getAccountByUserId($outflow["userId"], true);
        }
        if((round($account["cash"]*100)/100 - round($outflow["amount"]*100)/100)<0) {
            return false;
        }

        $outflow["cashType"] = "Coin";
        $outflow["type"] = "outflow";
        $outflow["sn"] = $this->makeSn();
        $outflow["createdTime"] = time();
        $outflow["cash"] = $account["cash"]-$outflow["amount"];

        $outflow = $this->getFlowDao()->addFlow($outflow);

        $this->getCashAccountService()->waveDownCashField($account["id"], $outflow["amount"]);

        return $outflow;
    }

    public function inflowByCoin($inflow)
    {
        if(!ArrayToolkit::requireds($inflow, array(
            'userId', 'amount', 'name', 'orderSn', 'category', 'note'
        ))){
            throw $this->createServiceException('参数缺失');
        }

        if(!is_numeric($inflow["amount"]) || $inflow["amount"] <= 0) {
            throw $this->createServiceException('金额必须为数字，并且不能小于0');
        }

        $account = $this->getCashAccountService()->getAccountByUserId($inflow["userId"]);
        if(empty($account)){
            $account = $this->getCashAccountService()->createAccount($inflow["userId"]);
            $account = $this->getCashAccountService()->getAccountByUserId($inflow["userId"], true);
        }

        $inflow["cashType"] = "Coin";
        $inflow["type"] = "inflow";
        $inflow["sn"] = $this->makeSn();
        $inflow["createdTime"] = time();
        $inflow["cash"] = $account["cash"]+$inflow["amount"];

        $inflow = $this->getFlowDao()->addFlow($inflow);

        $this->getCashAccountService()->waveCashField($account["id"], $inflow["amount"]);

        return $inflow;
    }

    public function inflowByRmb($inflow)
    {
        if(!ArrayToolkit::requireds($inflow, array(
            'userId', 'amount', 'name', 'orderSn', 'category', 'note'
        ))){
            throw $this->createServiceException('参数缺失');
        }

        if(!is_numeric($inflow["amount"]) || $inflow["amount"] <= 0) {
            throw $this->createServiceException('金额必须为数字，并且不能小于0');
        }

        $inflow["cashType"] = "RMB";
        $inflow["type"] = "inflow";
        $inflow["sn"] = $this->makeSn();
        $inflow["createdTime"] = time();

        $inflow = $this->getFlowDao()->addFlow($inflow);
        return $inflow;

    }

    public function outflowByRmb($outflow)
    {
        if(!ArrayToolkit::requireds($outflow, array(
            'userId', 'amount', 'name', 'orderSn', 'category', 'note'
        ))){
            throw $this->createServiceException('参数缺失');
        }

        if(!is_numeric($outflow["amount"]) || $outflow["amount"] <= 0) {
            throw $this->createServiceException('金额必须为数字，并且不能小于0');
        }

        $outflow["cashType"] = "RMB";
        $outflow["type"] = "outflow";
        $outflow["sn"] = $this->makeSn();
        $outflow["createdTime"] = time();

        $outflow = $this->getFlowDao()->addFlow($outflow);
        return $outflow;
    }

    public function findUserIdsByFlows($type,$createdTime,$orderBy, $start, $limit)
    {
        return $this->getFlowDao()->findUserIdsByFlows($type,$createdTime,$orderBy, $start, $limit);
    }

    public function findUserIdsByFlowsCount($type,$createdTime)
    {
        return $this->getFlowDao()->findUserIdsByFlowsCount($type,$createdTime);
    }

    public function changeRmbToCoin($rmbFlow)
    {
        $outflow = $this->outflowByRmb($rmbFlow);

        $coinSetting = $this->getSettingService()->get("coin");

        $coinRate = 1;
        if(!empty($coinSetting) && array_key_exists("cash_rate", $coinSetting)) {
            $coinRate = $coinSetting["cash_rate"];
        }

        $amount = $outflow["amount"] * $coinRate;

        $inflow = array(
            'userId' => $outflow["userId"],
            'amount' => $amount,
            'name' => "充值",
            'orderSn' => $outflow['orderSn'],
            'category' => 'change',
            'note' => '',
            'parentSn' => $outflow['sn']
        );

        $inflow["cashType"] = "Coin";
        $inflow["type"] = "inflow";
        $inflow["sn"] = $this->makeSn();
        $inflow["createdTime"] = time();

        $account = $this->getCashAccountService()->getAccountByUserId($inflow["userId"], true);
        if(empty($account)){
            $account = $this->getCashAccountService()->createAccount($inflow["userId"]);
            $account = $this->getCashAccountService()->getAccountByUserId($inflow["userId"], true);
        }

        $inflow["cash"] = $account["cash"]+$inflow["amount"];

        $inflow = $this->getFlowDao()->addFlow($inflow);

        $this->getCashAccountService()->waveCashField($account["id"], $inflow["amount"]);

        return $inflow;
    }

    private function makeSn()
    {
        return date('YmdHis') . rand(10000, 99999);
    }

    private function getNotifiactionService()
    {
        return $this->createService('User.NotificationService');
    }

    protected function getFlowDao()
    {
        return $this->createDao('Cash.CashFlowDao');
    }

    protected function getSettingService()
    {
        return $this->createService('System.SettingService');
    }

    protected function getCashAccountService()
    {
        return $this->createService('Cash.CashAccountService');
    }

}