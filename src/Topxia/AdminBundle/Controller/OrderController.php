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

    public function manageAction(Request $request, $targetType)
    {

        $conditions = $request->query->all();
        $conditions['targetType'] = $targetType;

        if (isset($conditions['keywordType'])) {
            $conditions[$conditions['keywordType']] = trim($conditions['keyword']);
        }

        if (!empty($conditions['startDateTime']) && !empty($conditions['endDateTime'])) {
            $conditions['startTime'] = strtotime($conditions['startDateTime']);
            $conditions['endTime'] = strtotime($conditions['endDateTime']);
        } 
        
        $paginator = new Paginator(
            $request,
            $this->getOrderService()->searchOrderCount($conditions),
            20
        ); 
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
            'targetType' => $targetType,
            'orders' => $orders,
            'users' => $users,
            'paginator' => $paginator,
        ));
    }

    public function detailAction(Request $request, $id)
    {
        return $this->forward('TopxiaWebBundle:Order:detail',array(
            'id' => $id,
        ));
    }

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

    /**
     *  导出订单
     * @param  string  $targetType    classroom | course | vip
     */
    public function exportCsvAction(Request $request, $targetType)
    {

        $conditions = $request->query->all();
        if(!empty($conditions['startTime']) && !empty($conditions['endTime'])) {
            $conditions['startTime'] = strtotime($conditions['startTime']);
            $conditions['endTime'] = strtotime($conditions['endTime']);
        }
        $conditions['targetType'] = $targetType;
        $status = array('created'=>'未付款','paid'=>'已付款','refunding'=>'退款中','refunded'=>'已退款','cancelled'=>'已关闭');
        $payment = array('alipay'=>'支付宝','wxpay'=>'微信支付','coin'=>'虚拟币支付','none'=>'--');
        $orders = $this->getOrderService()->searchOrders($conditions, array('createdTime', 'DESC'), 0, PHP_INT_MAX);

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
            $member .= $payment[$orders['payment']].",";
            $member .= date('Y-n-d H:i:s', $orders['createdTime']).",";
            if ($orders['paidTime'] != 0 ){
            $member .= date('Y-n-d H:i:s', $orders['paidTime']).",";
            }
            else{
            $member .= "-".","; 
            }
            $results[] = $member;
        }

        $str .= implode("\r\n", $results);
        $str = chr(239).chr(187).chr(191).$str;
        
        $filename = sprintf("%s-order-(%s).csv",$targetType,date('Y-n-d'));

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
