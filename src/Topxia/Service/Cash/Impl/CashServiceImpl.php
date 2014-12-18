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