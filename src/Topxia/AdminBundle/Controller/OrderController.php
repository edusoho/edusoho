<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class OrderController extends BaseController
{
    public function indexAction(Request $request)
    {
        return $this->render('TopxiaAdminBundle:Order:index.html.twig', array(
        ));
    }

    public function manageAction(Request $request, $type)
    {

        $conditions = $request->query->all();
        $conditions['targetType'] = $type;

        if (isset($conditions['keywordType'])) {
            $conditions[$conditions['keywordType']] = trim($conditions['keyword']);
        }
        $paginator = new Paginator(
            $request,
            $this->getOrderService()->searchOrderCount($conditions),
            20
        ); 

        if (!empty($conditions['startDateTime']) && !empty($conditions['endDateTime'])) {
            $conditions['startTime'] = strtotime($conditions['startDateTime']);
            $conditions['endTime'] = strtotime($conditions['endDateTime']);
        } 
        $orders = $this->getOrderService()->searchOrders(
        $conditions,
        array('createdTime', 'DESC'),
        $paginator->getOffsetCount(),
        $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($orders, 'userId'));

        foreach ($orders as $index => $expiredOrderToBeUpdated ){
            if ((($expiredOrderToBeUpdated["createdTime"] + 48*60*60) < time()) && ($expiredOrderToBeUpdated["status"]=='created')){
               $this->getOrderService()->cancelOrder($expiredOrderToBeUpdated['id']);
               $orders[$index]['status'] = 'cancelled';
            }
        }
        return $this->render('TopxiaAdminBundle:Order:manage.html.twig', array(
            'request' => $request,
            'type' => $type,
            'orders' => $orders,
            'users' => $users,
            'paginator' => $paginator,
        ));
    }

    // public function detailAction(Request $request, $id)
    // {
    //     $order = $this->getOrderService()->getOrder($id);
    //     $user = $this->getUserService()->getUser($order['userId']);

    //     $orderLogs = $this->getOrderService()->findOrderLogs($order['id']);

    //     $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($orderLogs, 'userId'));

    //     return $this->render('TopxiaAdminBundle:Order:detail-modal.html.twig', array(
    //         'order' => $order,
    //         'user' => $user,
    //         'orderLogs' => $orderLogs,
    //         'users' => $users,
    //     ));
    // }

    public function cancelRefundAction(Request $request, $id)
    {
        $this->getClassroomOrderService()->cancelRefundOrder($id);
        return $this->createJsonResponse(true);
    }

    public function auditRefundAction(Request $request, $id)
    {
        $order = $this->getOrderService()->getOrder($id);

        if ($request->getMethod() == 'POST') {
            $data = $request->request->all();

            $pass = $data['result'] == 'pass' ? true : false;
            $this->getOrderService()->auditRefundOrder($order['id'], $pass, $data['amount'], $data['note']);

            if ($pass) {
                if ($this->getClassroomService()->isClassroomStudent($order['targetId'], $order['userId'])) {
                    $this->getClassroomService()->removeStudent($order['targetId'], $order['userId']);
                }
            }

            $this->sendAuditRefundNotification($order, $pass, $data['amount'], $data['note']);

            return $this->createJsonResponse(true);
        }

        return $this->render('TopxiaAdminBundle:CourseOrder:refund-confirm-modal.html.twig', array(
            'order' => $order,
        ));

    }

    public function exportCsvAction(Request $request,$type)//classroom,course,vip订单
    {

        $conditions = $request->query->all();
        if(!empty($conditions['startTime']) && !empty($conditions['endTime'])) {
            $conditions['startTime'] = strtotime($conditions['startTime']);
            $conditions['endTime'] = strtotime($conditions['endTime']);
        }
        $conditions['targetType'] = $type;
        $status = array('created'=>'未付款','paid'=>'已付款','refunding'=>'退款中','refunded'=>'已退款','cancelled'=>'已关闭');
        $payment = array('alipay'=>'支付宝','wxpay'=>'微信支付','cion'=>'虚拟币支付','none'=>'--');
        // $userinfoFields = array('sn','status','targetType','amount','payment','createdTime','paidTime');

        $orders = $this->getOrderService()->searchOrders($conditions, array('createdTime', 'DESC'), 0,10);
        // $userFields = $this->getUserFieldService()->getAllFieldsOrderBySeqAndEnabled();
        // foreach ($userFields as $userField) {
        //     $fields[$userField['fieldName']] = $userField['title'];
        // }
        // $fields = array();
        // $userinfoFields = array_flip($userinfoFields);
        // $fields = array_intersect_key($fields, $userinfoFields);

        $studentUserIds = ArrayToolkit::column($orders, 'userId');

        $users = $this->getUserService()->findUsersByIds($studentUserIds);
        $users = ArrayToolkit::index($users, 'id');

        $profiles = $this->getUserService()->findUserProfilesByIds($studentUserIds);
        $profiles = ArrayToolkit::index($profiles, 'id');    

        $str = "订单号,订单状态,订单名称,购买者,姓名,实付价格,支付方式,创建时间,付款时间";

        // foreach ($fields as $key => $value) {
        //     $str .= ",".$value;
        // }
        $str .= "\r\n";

        $results = array();
        foreach ($orders as $key => $orders) {
            $member = "";
            $member .= $orders['sn'].",";
            $member .= $status[$orders['status']].",";
            $member .= $orders['title'].",";
            $member .= $users[$orders['userId']]['nickname'].",";
            $member .= $profiles[$orders['userId']]['truename'] ? $profiles[$orders['userId']]['truename']."," : "-".",";
            $member .= $orders['amount'].",";
            $member .= $payment[$orders['payment']].",";
            $member .= date('Y-n-d H:i:s', $orders['createdTime']).",";
            $member .= date('Y-n-d H:i:s', $orders['paidTime']).",";
            $results[] = $member;
        }

        $str .= implode("\r\n", $results);
        $str = chr(239).chr(187).chr(191).$str;
        
        $filename = sprintf("%s-order-(%s).csv",$type,date('Y-n-d'));

        $response = new Response();
        $response->headers->set('Content-type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$filename.'"');
        $response->headers->set('Content-length', strlen($str));
        $response->setContent($str);


        return $response;
    }

    public function coinExportCsvAction(Request $request)//coin
    {
        $conditions = $request->query->all();
        if(!empty($conditions['startTime']) && !empty($conditions['endTime'])) {
            $conditions['startTime'] = strtotime($conditions['startTime']);
            $conditions['endTime'] = strtotime($conditions['endTime']);
        }
        $status = array('created'=>'未付款','paid'=>'已付款','cancelled'=>'已关闭');
        $userinfoFields = array('sn','status','targetType','amount','payment','createdTime','paidTime');
        $orders = $this->getCashOrdersService()->searchOrders($conditions, array('createdTime', 'DESC'), 0,10);

        $studentUserIds = ArrayToolkit::column($orders, 'userId');

        $users = $this->getUserService()->findUsersByIds($studentUserIds);
        $users = ArrayToolkit::index($users, 'id');

        $profiles = $this->getUserService()->findUserProfilesByIds($studentUserIds);
        $profiles = ArrayToolkit::index($profiles, 'id');    

        $str = "订单号,订单状态,订单名称,购买者,姓名,实付价格,支付方式,创建时间,付款时间";

        $str .= "\r\n";

        $results = array();
        foreach ($orders as $key => $orders) {
            $member = "";
            $member .= $orders['sn'].",";
            $member .= $status[$orders['status']].",";
            $member .= $orders['title'].",";
            $member .= $users[$orders['userId']]['nickname'].",";
            $member .= $profiles[$orders['userId']]['truename'] ? $profiles[$orders['userId']]['truename']."," : "-".",";
            $member .= $orders['amount'].",";
            $member .= $orders['payment'].",";
            $member .= date('Y-n-d H:i:s', $orders['createdTime']).",";
            $member .= date('Y-n-d H:i:s', $orders['paidTime']).",";
            $results[] = $member;
        }

        $str .= implode("\r\n", $results);
        $str = chr(239).chr(187).chr(191).$str;
        
        $filename = sprintf("coin-order-(%s).csv",date('Y-n-d'));

        $response = new Response();
        $response->headers->set('Content-type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$filename.'"');
        $response->headers->set('Content-length', strlen($str));
        $response->setContent($str);


        return $response;

    }

    protected function sendAuditRefundNotification($order, $pass, $amount, $note)
    {
        $course = $this->getClassroomService()->getClassroom($order['targetId']);
        if (empty($course)) {
            return false;
        }

        if ($pass) {
            $message = $this->setting('refund.successNotification', '');
        } else {
            $message = $this->setting('refund.failedNotification', '');
        }

        if (empty($message)) {
            return false;
        }

        $classroomUrl = $this->generateUrl('classroom_show', array('id' => $classroom['id']));
        $variables = array(
            'classroom' => "<a href='{$classroomUrl}'>{$classroom['title']}</a>",
            'amount' => $amount,
            'note' => $note,
        );
        
        $message = StringToolkit::template($message, $variables);
        $this->getNotificationService()->notify($order['userId'], 'default', $message);

        return true;
    }

    protected function getOrderService()
    {
        return $this->getServiceKernel()->createService('Order.OrderService');
    }
   
    protected function getUserFieldService()
    {
        return $this->getServiceKernel()->createService('User.UserFieldService');
    }

    protected function getCashService(){
      
        return $this->getServiceKernel()->createService('Cash.CashService');
    }

    protected function getCashOrdersService(){
      
        return $this->getServiceKernel()->createService('Cash.CashOrdersService');
    }
}
