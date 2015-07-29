<?php

namespace Topxia\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Topxia\AdminBundle\Controller\BaseController;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
 
class CoinOrderController extends BaseController
{
    public function  ordersAction(Request $request){

        $fields = $request->query->all();
        $conditions=array();
        if(!empty($fields)){
          $conditions =$fields;
        }
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
        if (!empty($conditions['startDateTime']) && !empty($conditions['endDateTime'])) {

            $conditions['startTime'] = strtotime($conditions['startDateTime']);

            $conditions['endTime'] = strtotime($conditions['endDateTime']);
        } 

        $paginator = new Paginator(
            $this->get('request'),
            $a = $this->getCashOrdersService()->searchOrdersCount($conditions),
            20
          );

        $orders=$this->getCashOrdersService()->searchOrders(
            $conditions,
            array('createdTime','DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
          );

        foreach ($orders as $index => $expiredOrderToBeUpdated ){
            if ((($expiredOrderToBeUpdated["createdTime"] + 48*60*60) < time()) && ($expiredOrderToBeUpdated["status"]=='created')){
               $this->getOrderService()->cancelOrder($expiredOrderToBeUpdated['id']);
               $orders[$index]['status'] = 'cancelled';
            }
        }

        $userIds =  ArrayToolkit::column($orders, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);
        $type = 'coin';
        return $this->render('TopxiaAdminBundle:Coin:coin-orders.html.twig',array(
            'request' => $request,
            'users'=>$users,
            'type'=>$type,
            'orders'=>$orders,
            'paginator'=>$paginator,
          ));
    }

    public function logsAction($id)
    {
        $order = $this->getCashOrdersService()->getOrder($id);
        $user = $this->getUserService()->getUser($order['userId']);

        $orderLogs = $this->getCashOrdersService()->getLogsByOrderId($order['id']);
        
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($orderLogs, 'userId'));
        
        return $this->render('TopxiaAdminBundle:Coin:order-log-modal.html.twig', array(
            'order'=>$order,
            'user'=>$user,
            'orderLogs'=>$orderLogs,
            'users' => $users
        ));
    }


    protected function getCashOrdersService(){
      
        return $this->getServiceKernel()->createService('Cash.CashOrdersService');
    }


}
