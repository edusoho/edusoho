<?php

namespace Topxia\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Topxia\AdminBundle\Controller\BaseController;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
 
class CoinAdminController extends BaseController
{
    public function settingsAction(Request $request)
    {
        $postedParams = $request->request->all();

        $coinSettingsPosted = $this->getSettingService()->get('coin',array());

        $coinSettingsSaved = $coinSettingsPosted;
        $default = array(
          'coin_enabled' => 0,
          'coin_name' => '泰课币',
          'cash_rate' => 10,
          'coin_consume_range_and_present' => array(array(0,0))
        );
        $coinSettingsPosted = array_merge($default, $coinSettingsPosted);
      
        if ($request->getMethod() == 'POST') {
        $coinSettingsPosted = null;

        $coinSettingsPosted['coin_enabled'] = $request->request->get("coin_enabled");
        $coinSettingsPosted['coin_name'] = $request->request->get("coin_name");
        $coinSettingsPosted['cash_rate'] = $request->request->get("cash_rate");
        if (!is_numeric($coinSettingsPosted['cash_rate'])){
          $this->setFlashMessage('danger', '错误，填入的必须为数字！');
          return $this->settingsRenderedPage($coinSettingsSaved);
        }


        $i=1;
        foreach ($postedParams as $key => $value) {
          if($i>3){
            if (!is_numeric($value)){
              $this->setFlashMessage('danger', '错误，填入的必须为数字！');
              return $this->settingsRenderedPage($coinSettingsSaved);
            }
            $tmpArray[$i-4]=$value;
          }
          $i+=1;
        }
       
        for ($i=0; $i<count($tmpArray)/2 ; $i+=1) { 
          $oneRangePresent[0] = $tmpArray[2*$i];
          $oneRangePresent[1] = $tmpArray[2*$i+1];
          $coinConsumeRangeAndPresent[$i] = $oneRangePresent;
        }
   
        $coinSettingsPosted['coin_consume_range_and_present'] =  $coinConsumeRangeAndPresent;    
     
        $this->getSettingService()->set('coin', $coinSettingsPosted);
        $this->getLogService()->info('system', 'update_settings', "更新Coin虚拟币设置", $coinSettingsPosted);
        $this->setFlashMessage('success', '虚拟币设置已保存！');      
        }

        return $this->settingsRenderedPage($coinSettingsPosted);
    }

    public function  recordsAction(Request $request){
        $fields = $request->query->all();
        $conditions=array();
        if(!empty($fields)){
          $conditions =$fields;
        };
        
        if  (isset($conditions['keywordType'])) {
          if ($conditions['keywordType'] == 'userName'){
            $conditions['keywordType'] = 'userId';
            $userFindbyNickName = $this->getUserService()->getUserByNickname($conditions['keyword']);
            $conditions['keyword'] = $userFindbyNickName? $userFindbyNickName['id']:-1;
          }
        }
        if (isset($conditions['keywordType'])) {
          $conditions[$conditions['keywordType']] = $conditions['keyword'];
          unset($conditions['keywordType']);
          unset($conditions['keyword']);
        }

        $paginator = new Paginator(
            $this->get('request'),
            $this->getCashService()->searchFlowsCount($conditions),
            20
          );

        $cashes=$this->getCashService()->searchFlows(
            $conditions,
            array('createdTime','DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
          );

        $userIds =  ArrayToolkit::column($cashes, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);
        
        return $this->render('CoinBundle:Coin:coin-records.html.twig',array(
            'users'=>$users,
            'cashes'=>$cashes,
            'paginator'=>$paginator,
          ));
    }

    public function  ordersAction(Request $request){

        $fields = $request->query->all();
        $conditions=array();
        if(!empty($fields)){
          $conditions =$fields;
        };
        if  (isset($conditions['keywordType'])) {
          if ($conditions['keywordType'] == 'userName'){
            $conditions['keywordType'] = 'userId';
            $userFindbyNickName = $this->getUserService()->getUserByNickname($conditions['keyword']);
            $conditions['keyword'] = $userFindbyNickName? $userFindbyNickName['id']:-1;
          }
        }
        if (isset($conditions['keywordType'])) {
            $conditions[$conditions['keywordType']] = $conditions['keyword'];
            unset($conditions['keywordType']);
            unset($conditions['keyword']);
        }

        $paginator = new Paginator(
            $this->get('request'),
            $this->getCashOrdersService()->searchOrdersCount($conditions),
            20
          );

        $orders=$this->getCashOrdersService()->searchOrders(
            $conditions,
            array('createdTime','DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
          );

        $userIds =  ArrayToolkit::column($orders, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('CoinBundle:Coin:coin-orders.html.twig',array(
            'users'=>$users,
            'orders'=>$orders,
            'paginator'=>$paginator,
          ));
    }

    protected function settingsRenderedPage($coinSettings)
    {
      return $this->render('CoinBundle:Coin:coin-settings.html.twig',array(
        'coin_settings_posted' => $coinSettings,
        'range_number' => count($coinSettings['coin_consume_range_and_present']),
        'coin_consume_range_and_present' => $coinSettings['coin_consume_range_and_present']        
      ));
    }

    public function logsAction($id)
    {
        $order = $this->getCashOrdersService()->getOrder($id);
        $user = $this->getUserService()->getUser($order['userId']);

        $orderLogs = $this->getCashOrdersService()->getLogsByOrderId($order['id']);
        
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($orderLogs, 'userId'));
        
        return $this->render('CoinBundle:Coin:order-log-modal.html.twig', array(
            'order'=>$order,
            'user'=>$user,
            'orderLogs'=>$orderLogs,
            'users' => $users
        ));
    }

    public function giveCoinAction(Request $request)
    {
        if($request->getMethod()=="POST"){

            $fields=$request->request->all();

            $user=$this->getUserService()->getUserByNickname($fields['nickname']);

            $account=$this->getCashService()->getAccountByUserId($user["id"]);

            if(empty($account)){
                $account=$this->getCashService()->createAccount($user["id"]);
            }

            if($fields['type']=="add"){

                $this->getCashService()->waveCashField($account["id"],$fields['amount']);
                $this->getLogService()->info('cash', 'add_coin', "添加 ".$user['nickname']." {$fields['amount']} 虚拟币", array());

            }else{

                $this->getCashService()->waveDownCashField($account["id"],$fields['amount']);
                $this->getLogService()->info('cash', 'add_coin', "扣除 ".$user['nickname']." {$fields['amount']} 虚拟币", array());
            }
  
        }

        return $this->render('CoinBundle:Coin:order-create-modal.html.twig', array());

    }

    public function adminAction(Request $request)
    {   
        $fields = $request->query->all();
        $nickname="";
        $conditions=array();

        if(isset($fields['nickName']) && $fields['nickName']!= ""){
            $nickname =$fields['nickName'];
            $user = $this->getUserService()->getUserByNickname($nickname);

            if($user){
                $conditions=array('userId'=>$user['id']);
            }else{
                $conditions=array('userId'=>-1);
            }
            
        }

        $paginator = new Paginator(
            $this->get('request'),
            $this->getCashService()->searchAccountCount($conditions),
            20
          );

        $cashes=$this->getCashService()->searchAccount(
            $conditions,
            array(),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
          );

        $userIds =  ArrayToolkit::column($cashes, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('CoinBundle:Coin:coin-admin.html.twig', array(
            'cashes'=>$cashes,
            'users'=>$users,
            'paginator'=>$paginator,
        ));
    }

    public function editAction(Request $request,$id)
    {   
        if($request->getMethod()=="POST"){

            $fields=$request->request->all();

            $account=$this->getCashService()->getAccount($id);

            if($account){

                $user=$this->getUserService()->getUser($account['userId']);

                if($fields['type']=="add"){

                $this->getCashService()->waveCashField($id,$fields['amount']);

                $this->getLogService()->info('cash', 'add_coin', "添加 ".$user['nickname']." {$fields['amount']} 虚拟币", array());

                }else{

                    $this->getCashService()->waveDownCashField($id,$fields['amount']);
                    $this->getLogService()->info('cash', 'add_coin', "扣除 ".$user['nickname']." {$fields['amount']} 虚拟币", array());

                }
            }
  
        }

        return $this->render('CoinBundle:Coin:order-edit-modal.html.twig', array(
            'id'=>$id,
        ));
    }

    public function checkNicknameAction(Request $request)
    {
        $nickname = $request->query->get('value');
        $result = $this->getUserService()->isNicknameAvaliable($nickname);
        if ($result) {
            $response = array('success' => false, 'message' => '该用户不存在');
        } else {
            $response = array('success' => true, 'message' => '');
        }
        return $this->createJsonResponse($response);
    }

    protected function getSettingService(){

      return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getCashService(){
      
        return $this->getServiceKernel()->createService('Coin:Cash.CashService');
    }

    protected function getCashOrdersService(){
      
        return $this->getServiceKernel()->createService('Coin:Cash.CashOrdersService');
    }

    protected function getLogService() 
    {
        return $this->getServiceKernel()->createService('System.LogService');
    }


}
