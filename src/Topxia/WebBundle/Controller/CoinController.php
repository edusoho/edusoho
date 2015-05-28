<?php

namespace Topxia\WebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\StringToolkit;
use Topxia\Component\Payment\Payment;
use Topxia\WebBundle\Util\AvatarAlert;
use Symfony\Component\HttpFoundation\Response;
use Topxia\WebBundle\Controller\BaseController;
use Topxia\Common\Paginator;

class CoinController extends BaseController
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

        // $amount=$this->getOrderService()->analysisAmount(array('userId'=>$user->id,'status'=>'paid'));
        // $amount+=$this->getCashOrdersService()->analysisAmount(array('userId'=>$user->id,'status'=>'paid'));
        
        return $this->render('TopxiaWebBundle:Coin:index.html.twig',array(
          'payments' => $this->getEnabledPayments(),
          'account'=>$account,
          'cashes'=>$cashes,
          'paginator'=>$paginator,
          // 'amount'=>$amount,
          'ChargeCoin' => $ChargeCoin,
          'amountInflow' => $amountInflow?:0,
          'amountOutflow' => $amountOutflow?:0
        ));
    }

    public function cashBillAction(Request $request)
    {
        $user = $this->getCurrentUser();

        $conditions = array(
            'userId' => $user['id'],
        );

        $conditions['cashType'] = 'RMB';
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
            $request,
            $this->getCashService()->searchFlowsCount($conditions),
            20
        );

        $cashes = $this->getCashService()->searchFlows(
            $conditions,
            array('ID','DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        
        $conditions['type']  = 'inflow';      
        $amountInflow = $this->getCashService()->analysisAmount($conditions);

        $conditions['type']  = 'outflow'; 
        $amountOutflow = $this->getCashService()->analysisAmount($conditions);

        return $this->render('TopxiaWebBundle:Coin:cash_bill.html.twig',array(
            'cashes' => $cashes,
            'paginator' => $paginator,
            'amountInflow' => $amountInflow?:0,
            'amountOutflow' => $amountOutflow?:0            
          
        ));   
    }


    public function changeAction(Request $request)
    {   
        $user=$this->getCurrentUser();
        $userId=$user->id;

        $change=$this->getCashAccountService()->getChangeByUserId($userId);

        if(empty($change)){

            $change=$this->getCashAccountService()->addChange($userId);
        }

        $amount=$this->getOrderService()->analysisAmount(array('userId'=>$user->id,'status'=>'paid'));
        $amount+=$this->getCashOrdersService()->analysisAmount(array('userId'=>$user->id,'status'=>'paid'));
        
        $changeAmount=$amount-$change['amount'];

        list($canUseAmount,$canChange,$data)=$this->caculate($changeAmount,0,array());

        if($request->getMethod()=="POST"){

            if($canChange>0)
            $this->getCashAccountService()->changeCoin($changeAmount-$canUseAmount,$canChange,$userId);

            return $this->redirect($this->generateUrl('my_coin'));
        }

        return $this->render('TopxiaWebBundle:Coin:coin-change-modal.html.twig', array(
            'amount'=>$amount,
            'changeAmount'=>$changeAmount,
            'canChange'=>$canChange,
            'canUseAmount'=>$canUseAmount,
            'data'=>$data,
            ));
    }

    public function showAction(Request $request)
    {
        $coinSetting=$this->getSettingService()->get('coin',array());

        if (isset($coinSetting['coin_content'])) {
            $content=$coinSetting['coin_content'];
        }else{
             $content='';
        }

        return $this->render('TopxiaWebBundle:Coin:coin-content-show.html.twig', array(
            'content'=>$content,
            'coinSetting'=>$coinSetting
            ));
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

    public function payAction(Request $request)
    {
        $formData = $request->request->all();
        $user = $this->getCurrentUser();
        $formData['payment']="alipay";
        $formData['userId']=$user['id'];

        $order=$this->getCashOrdersService()->addOrder($formData);
        $payRequestParams = array(
            'returnUrl' => $this->generateUrl('coin_order_pay_return',array('name'=>$order['payment']),true),
            'notifyUrl' => $this->generateUrl('coin_order_pay_notify',array('name'=>$order['payment']),true),
            'showUrl' => $this->generateUrl('my_coin',array(),true),
        );

        return $this->forward('TopxiaWebBundle:Coin:submitPayRequest', array(
                'order' => $order,
                'requestParams' => $payRequestParams,
            ));
    }

    public function submitPayRequestAction(Request $request , $order, $requestParams)
    {
        $paymentRequest = $this->createPaymentRequest($order, $requestParams);
        
        return $this->render('TopxiaWebBundle:Coin:submit-pay-request.html.twig', array(
            'form' => $paymentRequest->form(),
            'order' => $order,
        ));
    }


    private function createPaymentRequest($order, $requestParams)
    {
        $options = $this->getPaymentOptions($order['payment']);
        $request = Payment::createRequest($order['payment'], $options);

        $requestParams = array_merge($requestParams, array(
            'orderSn' => $order['sn'],
            'title' => $order['title'],
            'summary' => '',
            'amount' => $order['amount'],
        ));
        return $request->setParams($requestParams);
    }


    public function payReturnAction(Request $request,$name)
    {
        $this->getLogService()->info('order', 'pay_result',  "{$name}页面跳转支付通知", $request->query->all());
        $response = $this->createPaymentResponse($name, $request->query->all());

        $payData = $response->getPayData();

        if ($payData['status'] == "waitBuyerConfirmGoods") {
            return $this->forward("TopxiaWebBundle:Coin:resultNotice");
        }

        list($success, $order) = $this->getCashOrdersService()->payOrder($payData);

        if ($order['status'] == 'paid' and $success) {
            $successUrl = $this->generateUrl('my_coin', array(), true);
        }

        $goto = empty($successUrl) ? $this->generateUrl('homepage', array(), true) : $successUrl;
        return $this->redirect($goto);
    }

    public function resultNoticeAction(Request $request)
    {
        return $this->render('TopxiaWebBundle:Coin:retrun-notice.html.twig');
    }
    
    public function payNotifyAction(Request $request,$name)
    {
        $this->getLogService()->info('order', 'pay_result', "{$name}服务器端支付通知", $request->request->all());
        $response = $this->createPaymentResponse($name, $request->request->all());

        $payData = $response->getPayData();
        try {
            list($success, $order) = $this->getCashOrdersService()->payOrder($payData);

            return new Response('success');
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function createPaymentResponse($name, $params)
    {
        $options = $this->getPaymentOptions($name);
        $response = Payment::createResponse($name, $options);

        return $response->setParams($params);
    }

    private function getPaymentOptions($payment)
    {
        $settings = $this->setting('payment');

        if (empty($settings)) {
            throw new \RuntimeException('支付参数尚未配置，请先配置。');
        }

        if (empty($settings['enabled'])) {
            throw new \RuntimeException("支付模块未开启，请先开启。");
        }

        if (empty($settings[$payment. '_enabled'])) {
            throw new \RuntimeException("支付模块({$payment})未开启，请先开启。");
        }

        if (empty($settings["{$payment}_key"]) or empty($settings["{$payment}_secret"])) {
            throw new \RuntimeException("支付模块({$payment})参数未设置，请先设置。");
        }

        $options = array(
            'key' => $settings["{$payment}_key"],
            'secret' => $settings["{$payment}_secret"],
            'type' => $settings["{$payment}_type"]
        );

        return $options;
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

    protected function getCashService(){
      
        return $this->getServiceKernel()->createService('Cash.CashService');
    }

    protected function getCashAccountService()
    {
        return $this->getServiceKernel()->createService('Cash.CashAccountService');
    }

    protected function getCashOrdersService(){
      
        return $this->getServiceKernel()->createService('Cash.CashOrdersService');
    }

    protected function getOrderService()
    {
        return $this->getServiceKernel()->createService('Order.OrderService');
    }

    protected function getSettingService(){

      return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getAppService()
    {
        return $this->getServiceKernel()->createService('CloudPlatform.AppService');
    }
}
