<?php
namespace Topxia\Service\Cash\Impl;

use TopXia\Service\Common\BaseService;
use Topxia\Service\Cash\CashService;
use TopXia\Common\ArrayToolkit;
use TopXia\Service\Util\EasyValidator;

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
    public function outFlowByCoin($outFlow)
    {
        if(!ArrayToolkit::requireds($outFlow, array(
            'userId', 'amount', 'name', 'orderSn', 'category', 'note'
        ))){
            throw $this->createServiceException('参数缺失');
        }

        if(!is_numeric($outFlow["amount"]) || $outFlow["amount"] <= 0) {
            throw $this->createServiceException('金额必须为数字，并且不能小于0');
        }

        $account = $this->getCashAccountService()->getAccountByUserId($outFlow["userId"]);

        if($account["cash"] < $outFlow["amount"]) {
            throw $this->createServiceException('余额不足');
        }

        $outFlow["cashType"] = "Coin";
        $outFlow["type"] = "outflow";
        $outFlow["sn"] = $this->makeSn();
        $outFlow["createdTime"] = time();
        $outFlow["cash"] = $account["cash"]-$outFlow["amount"];

        $outFlow = $this->getFlowDao()->addFlow($outFlow);

        $this->getCashAccountService()->waveDownCashField($account["id"], $outFlow["amount"]);

        return $outFlow;
    }

    public function inFlowByRmb($inFlow)
    {
        if(!ArrayToolkit::requireds($inFlow, array(
            'userId', 'amount', 'name', 'orderSn', 'category', 'note'
        ))){
            throw $this->createServiceException('参数缺失');
        }

        if(!is_numeric($inFlow["amount"]) || $inFlow["amount"] <= 0) {
            throw $this->createServiceException('金额必须为数字，并且不能小于0');
        }

        $inFlow["cashType"] = "RMB";
        $inFlow["type"] = "inflow";
        $inFlow["sn"] = $this->makeSn();
        $inFlow["createdTime"] = time();

        $inFlow = $this->getFlowDao()->addFlow($inFlow);
        return $inFlow;

    }

    public function outFlowByRmb($outFlow)
    {
        if(!ArrayToolkit::requireds($outFlow, array(
            'userId', 'amount', 'name', 'orderSn', 'category', 'note'
        ))){
            throw $this->createServiceException('参数缺失');
        }

        if(!is_numeric($outFlow["amount"]) || $outFlow["amount"] <= 0) {
            throw $this->createServiceException('金额必须为数字，并且不能小于0');
        }

        $outFlow["cashType"] = "RMB";
        $outFlow["type"] = "outflow";
        $outFlow["sn"] = $this->makeSn();
        $outFlow["createdTime"] = time();

        $outFlow = $this->getFlowDao()->addFlow($outFlow);
        return $outFlow;
    }

    public function changeRmbToCoin($rmbFlow)
    {
        $outFlow = $this->outFlowByRmb($rmbFlow);

        $coinSetting = $this->getSettingService()->get("coin");

        $coinRate = 1;
        if(!empty($coinSetting) && array_key_exists("cash_rate", $coinSetting)) {
            $coinRate = $coinSetting["cash_rate"];
        }

        $amount = $outFlow["amount"] * $coinRate;

        $inFlow = array(
            'userId' => $outFlow["userId"],
            'amount' => $amount,
            'name' => "充值",
            'orderSn' => $outFlow['orderSn'],
            'category' => 'change',
            'note' => '',
            'parentSn' => $outFlow['sn']
        );

        $inFlow["cashType"] = "Coin";
        $inFlow["type"] = "inflow";
        $inFlow["sn"] = $this->makeSn();
        $inFlow["createdTime"] = time();

        $account = $this->getCashAccountService()->getAccountByUserId($inFlow["userId"]);
        $inFlow["cash"] = $account["cash"]+$inFlow["amount"];

        $inFlow = $this->getFlowDao()->addFlow($inFlow);

        $this->getCashAccountService()->waveCashField($account["id"], $inFlow["amount"]);

        return $inFlow;
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