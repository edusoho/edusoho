<?php
namespace Topxia\Service\Cash\Impl;

use TopXia\Service\Common\BaseService;
use Topxia\Service\Cash\CashService;
use TopXia\Common\ArrayToolkit;
use TopXia\Service\Util\EasyValidator;

class CashServiceImpl extends BaseService implements CashService
{
    public function outflow($userId, $flow) 
    {
        $inflow=array(
            'userId'=>$userId,
            'sn'=>$this->makeSn(),
            'type'=>'outflow',
            'amount'=> $flow["amount"],
            'name'=>$flow['name'],
            'orderSn'=>$flow['sn'],
            'category'=>$flow['category'],
            'note'=>$flow['note'],
            'createdTime'=>time(),
        );

        $inflow = $this->getFlowDao()->addFlow($inflow);
    }

    public function inflow($userId, $flow)
    {   
        $coinSetting=$this->getSettingService()->get('coin',array());

        if(!is_numeric($flow['amount']))
        {
            throw $this->createServiceException('充值金额必须为整数!');
            
        }

        if(empty($flow['name']) || empty($flow['sn'])|| empty($flow['category']))
        {
            throw $this->createServiceException('必要字段不能为空!');
            
        }

        try {

            $this->getAccountDao()->getConnection()->beginTransaction();

            $account = $this->getCashAccountService()->getAccountByUserId($userId, true);
            if (empty($account)) {
                throw $this->createServiceException("Account #{$userId} is not exist.");
            }

            if (intval($flow['amount']*100) <= 0) {
                throw $this->createServiceException("Amount #{$flow['amount']} is error.");
            }

            $coin=$coinSetting['cash_rate']*$flow['amount'];
            $inflow = array();
            $inflow=array(
                'userId'=>$userId,
                'sn'=>$this->makeSn(),
                'type'=>'inflow',
                'amount'=>$coin,
                'name'=>$flow['name'],
                'orderSn'=>$flow['sn'],
                'category'=>$flow['category'],
                'note'=>$flow['note'],
                'createdTime'=>time(),
            );

            $inflow = $this->getFlowDao()->addFlow($inflow);

            $this->getAccountDao()->waveCashField($account['id'], $coin);

            $this->getNotifiactionService()->notify($userId, 'default', "您已成功充值".$coin.$coinSetting['coin_name'].",前往 <a href='/my/coin'>我的账户</a> 查看");
           
            $this->getAccountDao()->getConnection()->commit();

            return $inflow;
        } catch (\Exception $e) {
            $this->getAccountDao()->getConnection()->rollback();

            throw $e;
        }
    }

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

        $coinRate = $this->getSettingService()->get("coin.cash_rate");

        if(!$coinRate) {
            $coinRate = 1;
        }

        $amount = $outFlow["amount"] * $coinRate;

        $inFlow = array(
            'userId' => $outFlow["userId"],
            'amount' => $amount,
            'name' => "充值",
            'orderSn' => $outFlow['orderSn'],
            'category' => 'change',
            'note' => ''
        );

        $inFlow["cashType"] = "Coin";
        $inFlow["type"] = "inflow";
        $inFlow["sn"] = $this->makeSn();
        $inFlow["createdTime"] = time();

        $inFlow = $this->getFlowDao()->addFlow($inFlow);

        $account = $this->getCashAccountService()->getAccountByUserId($inFlow["userId"]);
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