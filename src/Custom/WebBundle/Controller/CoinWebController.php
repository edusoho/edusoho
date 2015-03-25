<?php

namespace Custom\WebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\StringToolkit;
use Topxia\Component\Payment\Payment;
use Topxia\WebBundle\Util\AvatarAlert;
use Symfony\Component\HttpFoundation\Response;
use Topxia\WebBundle\Controller\CoinController;
use Topxia\Common\Paginator;

class CoinWebController extends CoinController
{
    public function indexAction(Request $request)
    {   
        $user = $this->getCurrentUser();

        if(!$user->isLogin()) {
            return $this->createMessageResponse('error', '用户未登录，请先登录！');
        }

        $coinEnabled = $this->setting("coin.coin_enabled");
        if(empty($coinEnabled) || $coinEnabled == 0) {
            return $this->createMessageResponse('error', '网校虚拟币未开启！');
        }

        $account = $this->getCashAccountService()->getAccountByUserId($user->id,true);

        $ChargeCoin = $this->getAppService()->findInstallApp('ChargeCoin');
        
        if(empty($account)){
            $this->getCashAccountService()->createAccount($user->id);
        }
        
        $fields = $request->query->all();      
        $conditions = array();

        if(!empty($fields)){
            $conditions = $fields;
        }

        $conditions['cashType'] = 'Coin';
        $conditions['userId'] = $user->id;

        $conditions['startTime'] = 0; 
        $conditions['endTime'] = time();
        switch ($request->get('lastHowManyMonths')) { 
            case 'oneWeek': 
                $conditions['startTime'] = $conditions['endTime']-7*24*3600; 
                break; 
            case 'twoWeeks': 
                $conditions['startTime'] = $conditions['endTime']-14*24*3600; 
                break; 
            case 'oneMonth': 
                $conditions['startTime'] = $conditions['endTime']-30*24*3600;               
                break;     
            case 'twoMonths': 
                $conditions['startTime'] = $conditions['endTime']-60*24*3600;               
                break;   
            case 'threeMonths': 
                $conditions['startTime'] = $conditions['endTime']-90*24*3600;               
                break;  
        } 
        

        $paginator = new Paginator(
            $this->get('request'),
            $this->getCashService()->searchFlowsCount($conditions),
            20
        );

        $cashes=$this->getCashService()->searchFlows(
            $conditions,
            array('ID','DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $conditions['type']  = 'inflow';      
        $amountInflow = $this->getCashService()->analysisAmount($conditions);

        $conditions['type']  = 'outflow'; 
        $amountOutflow = $this->getCashService()->analysisAmount($conditions);

         $amount = $this->getOrderService()->analysisAmount(array('userId'=>$user->id,'status'=>'paid','payment'=>'alipay'));
         $amount += $this->getCashOrdersService()->analysisAmount(array('userId'=>$user->id,'status'=>'paid'));
        
        return $this->render('CustomWebBundle:Coin:index.html.twig',array(
          'payments' => $this->getEnabledPayments(),
          'account'=>$account,
          'cashes'=>$cashes,
          'paginator'=>$paginator,
           'amount'=>$amount,
          'ChargeCoin' => $ChargeCoin,
          'amountInflow' => $amountInflow?:0,
          'amountOutflow' => $amountOutflow?:0
        ));
    }
    private function getEnabledPayments()
    {
        $enableds = array();

        $setting = $this->setting('payment', array());

        if (empty($setting['enabled'])) {
            return $enableds;
        }

        $payNames = array('alipay');
        foreach ($payNames as $payName) {
            if (!empty($setting[$payName . '_enabled'])) {
                $enableds[$payName] = array(
                    'type' => empty($setting[$payName . '_type']) ? '' : $setting[$payName . '_type'],
                );
            }
        }

        return $enableds;
    }
}
