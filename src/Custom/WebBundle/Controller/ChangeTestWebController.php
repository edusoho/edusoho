<?php

namespace Custom\WebBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\AdminBundle\Controller\CoinController;
use Topxia\Common\ArrayToolkit;

class ChangeTestWebController extends CoinController
{
    public function indexAction(Request $request,$startnum)
    {

        $users = $this->getUserService()->AllUser();

        $usersids = ArrayToolkit::column($users,'id');
        
        for($i = $startnum; $i < $startnum+1000; $i ++)
        {   
            $userId = $usersids[$i];
            $change=$this->getCashAccountService()->getChangeByUserId($userId);
            if(empty($change))
            {
                $change=$this->getCashAccountService()->addChange($userId);
            }

            $amount=$this->getOrderService()->analysisAmount(array('userId'=>$userId,'status'=>'paid'));

            $amount+=$this->getCashOrdersService()->analysisAmount(array('userId'=>$userId,'status'=>'paid'));
            $changeAmount=$amount-$change['amount'];
            if($changeAmount < 100)
            {
                    continue;
            }
            list($canUseAmount,$canChange,$data)=$this->caculate($changeAmount,0,array());

            $account=$this->getCashAccountService()->getAccountByUserId($userId,true);
        
            if(empty($account)){
                $this->getCashAccountService()->createAccount($userId);
            }
            if($canChange>0)
                $this->getCashAccountService()->changeCoin($changeAmount-$canUseAmount,$canChange,$userId);

        }
        return $this->createJsonResponse(true);
    }

    private function caculate($amount,$canChange,$data)
    {
            $coinSetting= $this->getSettingService()->get('coin',array());

            $coinRanges=$coinSetting['coin_consume_range_and_present'];
            if($coinRanges==array(array(0,0))) return array($amount,$canChange,$data);
            for($i=0;$i<count($coinRanges);$i++){

                $consume=$coinRanges[$i][0];
                $change=$coinRanges[$i][1];

                foreach ($coinRanges as $key => $range) {
                    
                    if($change==$range[1] && $consume>$range[0]){

                        $consume=$range[0];
                    }
                }

                $ranges[]=array($consume,$change);
            }
            
            $ranges =  ArrayToolkit::index($ranges, 1);
        
            $send=0;
            $bottomConsume=0;
            foreach ($ranges as $key => $range) {
              
               if($amount>=$range[0] && $send<$range[1]){
                    $send=$range[1];
               }

               if($bottomConsume>$range[0] || $bottomConsume==0){
                    $bottomConsume=$range[0];
               }
                
            }

            if(isset($ranges[$send]) && $amount>=$ranges[$send][0]){
                $canUseAmount=$amount-$ranges[$send][0];
                $canChange+=$send;
            }else{
                $canUseAmount=$amount;
                $canChange+=$send;
            }
            
            if($send>0){
                $data[]=array(
                'send'=>"消费满{$ranges[$send][0]}元送{$ranges[$send][1]}",
                'sendAmount'=>"{$ranges[$send][1]}",);
            }

            if($canUseAmount>=$bottomConsume){
               list($canUseAmount,$canChange,$data)=$this->caculate($canUseAmount,$canChange,$data);

            }

       return array($canUseAmount,$canChange,$data);

    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('Custom:User.AllUserService');
    }

    protected function getCashService()
    {
        return $this->getServiceKernel()->createService('Cash.CashService');
    }

    protected function getCashAccountService()
    {
        return $this->getServiceKernel()->createService('Cash.CashAccountService');
    }

    protected function getCashOrdersService()
    {  
        return $this->getServiceKernel()->createService('Cash.CashOrdersService');
    }

    protected function getOrderService()
    {
        return $this->getServiceKernel()->createService('Order.OrderService');
    }

    protected function getSettingService(){

      return $this->getServiceKernel()->createService('System.SettingService');
    }


}
