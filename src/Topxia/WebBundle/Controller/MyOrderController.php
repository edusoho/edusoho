<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class MyOrderController extends BaseController
{
    private function getTimeRange($fields)
    {
        if(isset($fields['startTime'])&&isset($fields['endTime'])&&$fields['startTime']!=""&&$fields['endTime']!="")
        {   
            if($fields['startTime']>$fields['endTime']) return false;
            return array('startTime'=>strtotime($fields['startTime']),'endTime'=>(strtotime($fields['endTime'])+24*3600));
        }

        return array('startTime'=>strtotime(date("Y-m",time())),'endTime'=>strtotime(date("Y-m-d",time()+24*3600)));
    }

    public function indexAction (Request $request)
    {
    	$user = $this->getCurrentUser();

    	$conditions = array(
    		'userId' => $user['id'],
            'status' => $request->get('status')
		);

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
            $this->getOrderService()->searchOrderCount($conditions),
            20
        );

        $orders = $this->getOrderService()->searchOrders(
        	$conditions,
        	'latest',
        	$paginator->getOffsetCount(),
        	$paginator->getPerPageCount()
    	);

        $waitToBePaidCountConditions = array('userId' => $user['id'],'status' => 'created');
        $waitToBePaidCount = $this->getOrderService()->searchOrderCount($waitToBePaidCountConditions);

        foreach ($orders as $index => $expiredOrderToBeUpdated ){
            if ((($expiredOrderToBeUpdated["createdTime"] + 48*60*60) < time()) && ($expiredOrderToBeUpdated["status"]=='created')){
               $this->getOrderService()->cancelOrder($expiredOrderToBeUpdated['id']);
               $orders[$index]['status'] = 'cancelled'; 
               $waitToBePaidCount -= 1;
            }
        }
        
        return $this->render('TopxiaWebBundle:MyOrder:index.html.twig',array(
        	'orders' => $orders,
            'paginator' => $paginator,
            'request' => $request,
            'waitToBePaidCount' => $waitToBePaidCount,
        ));   
    }

    public  function detailAction(Request $request, $id)
    {
        $currentUser = $this->getCurrentUser();
        $order = $this->getOrderService()->getOrder($id);
        if ($currentUser['id'] != $order['userId'] ){
            throw new \RuntimeException("普通用户不能查看别人的订单");
        }
        $user = $this->getUserService()->getUser($order['userId']);

        $orderLogs = $this->getOrderService()->findOrderLogs($order['id']);

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($orderLogs, 'userId'));
        
        return $this->render('TopxiaWebBundle:MyOrder:detail-modal.html.twig', array(
            'order'=>$order,
            'user'=>$user,
            'orderLogs'=>$orderLogs,
            'users' => $users
        ));
    }


    public function refundsAction(Request $request)
    {
    	$user = $this->getCurrentUser();

        $paginator = new Paginator(
            $request,
            $this->getOrderService()->findUserRefundCount($user['id']),
            20
        );

        $refunds = $this->getOrderService()->findUserRefunds(
        	$user['id'],
        	$paginator->getOffsetCount(),
        	$paginator->getPerPageCount()
    	);

    	$orders = $this->getOrderService()->findOrdersByIds(ArrayToolkit::column($refunds, 'orderId'));

        return $this->render('TopxiaWebBundle:MyOrder:refunds.html.twig',array(
        	'refunds' => $refunds,
        	'orders' => $orders,
            'paginator' => $paginator
        ));
    }

    public function cancelRefundAction(Request $request, $id)
    {
        $this->getCourseOrderService()->cancelRefundOrder($id);
        return $this->createJsonResponse(true);
    }

    public function cancelAction(Request $request, $id)
    {
        $this->getCourseOrderService()->cancelOrder($id);
        return $this->createJsonResponse(true);
    }

    private function getOrderService()
    {
        return $this->getServiceKernel()->createService('Order.OrderService');
    }

    private function getCourseOrderService()
    {
        return $this->getServiceKernel()->createService('Course.CourseOrderService');
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

}