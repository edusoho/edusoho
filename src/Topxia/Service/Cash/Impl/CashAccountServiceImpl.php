<?php
namespace Topxia\Service\Cash\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Cash\CashAccountService;
use Topxia\Common\ArrayToolkit;

class CashAccountServiceImpl extends BaseService implements CashAccountService
{
	public function createAccount($userId)
    {
        $fields = array('userId' => $userId, 'cash' => 0);
        return $this->getAccountDao()->addAccount($fields);
    }

    public function getAccountByUserId($userId, $lock = false)
    {
        return $this->getAccountDao()->getAccountByUserId($userId, $lock);
    }

    public function getAccount($id)
    {
        return $this->getAccountDao()->getAccount($id);
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

            $message = array(
                'value' => $coinAmount,
                'type' => 'changing'
                );
            $this->getNotificationService()->notify($userId, 'cash-account', $message);
           
            $this->getAccountDao()->getConnection()->commit();

            return $inflow;
        } catch (\Exception $e) {
            $this->getAccountDao()->getConnection()->rollback();

            throw $e;
        }
    }

	public function searchAccount($conditions, $orderBy, $start, $limit)
    {
        return $this->getAccountDao()->searchAccount($conditions, $orderBy, $start, $limit);
    }

    public function searchAccountCount($conditions)
    {
        return $this->getAccountDao()->searchAccountCount($conditions);
    }

    public function waveCashField($id, $value)
    {   
        if(!is_numeric($value))
        {
            throw $this->createServiceException('充值金额必须为整数!');
            
        }
        $coinSetting=$this->getSettingService()->get('coin',array());
        $coinSetting['coin_name'] = isset($coinSetting['coin_name'])? $coinSetting['coin_name']:"虚拟币";

        $account=$this->getAccount($id);
        $message = array(
            'value' => $value,
            'type' => 'changeOk');
        $this->getNotificationService()->notify($account['userId'], 'cash-account', $message);
           
        return $this->getAccountDao()->waveCashField($id, $value);
    }

    public function waveDownCashField($id, $value)
    {   
        if(!is_numeric($value))
        {
            throw $this->createServiceException('充值金额必须为整数!');
        }
        
        $coinSetting=$this->getSettingService()->get('coin',array());
        $coinSetting['coin_name'] = isset($coinSetting['coin_name'])? $coinSetting['coin_name']:"虚拟币";
        $account=$this->getAccountDao()->getAccount($id);

        
         $message = array(
            'value' => $value,
            'type' => 'deduct');
        $this->getNotificationService()->notify($account['userId'], 'cash-account', $message);

        return $this->getAccountDao()->waveDownCashField($id, $value);
    }

    public function reward($amount,$name,$userId,$type=null)
    {   
        $coinSetting=$this->getSettingService()->get('coin',array());

        try {

            $this->getAccountDao()->getConnection()->beginTransaction();

            $account = $this->getAccountDao()->getAccountByUserId($userId, true);
            
            if(empty($account)){
                $account = $this->createAccount($userId);
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

    protected function getSettingService()
    {
        return $this->createService('System.SettingService');
    }

    protected function makeSn()
    {
        return date('YmdHis') . rand(10000, 99999);
    }

    protected function getNotificationService()
    {
        return $this->createService('User.NotificationService');
    }

    protected function getAccountDao()
    {
        return $this->createDao('Cash.CashAccountDao');
    }

    protected function getChangeDao()
    {
        return $this->createDao('Cash.CashChangeDao');
    }

    protected function getFlowDao()
    {
        return $this->createDao('Cash.CashFlowDao');
    }
}