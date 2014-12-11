<?php
namespace Topxia\Service\Cash\Impl;

use TopXia\Service\Common\BaseService;
use Topxia\Service\Cash\CashService;
use TopXia\Common\ArrayToolkit;
use TopXia\Service\Util\EasyValidator;

class CashServiceImpl extends BaseService implements CashService
{
    public function createAccount($userId)
    {
        $fields = array('userId' => $userId, 'cash' => 0);
        return $this->getAccountDao()->addAccount($fields);
    }

    public function getAccountByUserId($userId)
    {
        return $this->getAccountDao()->getAccountByUserId($userId);
    }

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

            $account = $this->getAccountDao()->getAccountByUserId($userId, true);
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

    public function waveCashField($id, $value)
    {   
        if(!is_numeric($value))
        {
            throw $this->createServiceException('充值金额必须为整数!');
            
        }
        $coinSetting=$this->getSettingService()->get('coin',array());
        $account=$this->getAccountDao()->getAccount($id);
        $this->getNotifiactionService()->notify($account['userId'], 'default', "您已成功充值".$value.$coinSetting['coin_name'].",前往 <a href='/my/coin'>我的账户</a> 查看");
           
        return $this->getAccountDao()->waveCashField($id, $value);
    }

    public function getAccount($id)
    {
        return $this->getAccountDao()->getAccount($id);
    }

    public function waveDownCashField($id, $value)
    {   
        if(!is_numeric($value))
        {
            throw $this->createServiceException('充值金额必须为整数!');
            
        }
        
        $coinSetting=$this->getSettingService()->get('coin',array());
        $account=$this->getAccountDao()->getAccount($id);
        $this->getNotifiactionService()->notify($account['userId'], 'default', "您被扣除".$value.$coinSetting['coin_name'].",前往 <a href='/my/coin'>我的账户</a> 查看");
        

        return $this->getAccountDao()->waveDownCashField($id, $value);
    }

    public function reward($amount,$name,$userId,$type=null)
    {   
        $coinSetting=$this->getSettingService()->get('coin',array());

        try {

            $this->getAccountDao()->getConnection()->beginTransaction();

            $account = $this->getAccountDao()->getAccountByUserId($userId, true);
            
            if(empty($account)){
            $this->createAccount($userId);
            }

            $inflow = array();

            if($type=="cut"){

                $inflow=array(
                'userId'=>$userId,
                'sn'=>$this->makeSn(),
                'type'=>'outflow',
                'amount'=>$amount,
                'name'=>$name,
                'category'=>"exchange",
                'orderSn'=>'R'.$this->makeSn(),
                'createdTime'=>time(),
                );

                $inflow = $this->getFlowDao()->addFlow($inflow);

                $this->getAccountDao()->waveDownCashField($account['id'], $amount);

            }else{

                $inflow=array(
                'userId'=>$userId,
                'sn'=>$this->makeSn(),
                'type'=>'inflow',
                'amount'=>$amount,
                'name'=>$name,
                'category'=>"exchange",
                'orderSn'=>'R'.$this->makeSn(),
                'createdTime'=>time(),
                );

                $inflow = $this->getFlowDao()->addFlow($inflow);

                $this->getAccountDao()->waveCashField($account['id'], $amount);
            }
            
            $this->getAccountDao()->getConnection()->commit();

            return $inflow;
        } catch (\Exception $e) {
            $this->getAccountDao()->getConnection()->rollback();

            throw $e;
        }
    }
    
    public function getChangeByUserId($userId)
    {
        return $this->getChangeDao()->getChangeByUserId($userId);
    }

    public function addChange($userId)
    {
        $fields=array(
            'userId'=>$userId,
            'amount'=>0,
            );

        return $this->getChangeDao()->addChange($fields);
    }

    public function changeCoin($amount,$coinAmount,$userId)
    {   
        $coinSetting=$this->getSettingService()->get('coin',array());

        try {

            $this->getAccountDao()->getConnection()->beginTransaction();

            $account = $this->getAccountDao()->getAccountByUserId($userId, true);
            if (empty($account)) {
                throw $this->createServiceException("Account #{$userId} is not exist.");
            }

            $inflow = array();
            $inflow=array(
                'userId'=>$userId,
                'sn'=>$this->makeSn(),
                'type'=>'inflow',
                'amount'=>$coinAmount,
                'name'=>"兑换".$coinAmount.$coinSetting['coin_name'],
                'category'=>"exchange",
                'orderSn'=>'E'.$this->makeSn(),
                'createdTime'=>time(),
                );

            $inflow = $this->getFlowDao()->addFlow($inflow);

            $this->getAccountDao()->waveCashField($account['id'], $coinAmount);

            $change=$this->getChangeDao()->getChangeByUserId($userId,true);
            
            $this->getChangeDao()->waveCashField($change['id'], $amount);

            $this->getNotifiactionService()->notify($userId, 'default', "您已成功兑换".$coinAmount.$coinSetting['coin_name'].",前往 <a href='/my/coin'>我的账户</a> 查看");
           
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
    
    public function searchAccount($conditions, $orderBy, $start, $limit)
    {
        return $this->getAccountDao()->searchAccount($conditions, $orderBy, $start, $limit);
    }

    public function searchAccountCount($conditions)
    {
        return $this->getAccountDao()->searchAccountCount($conditions);
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

    protected function getAccountDao()
    {
        return $this->createDao('Cash.CashAccountDao');
    }

    protected function getChangeDao()
    {
        return $this->createDao('Cash.CashChangeDao');
    }

    protected function getSettingService(){
      
        return $this->createService('System.SettingService');
    }

}