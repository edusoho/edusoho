<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\StringToolkit;
use Topxia\Service\Order\OrderRefundProcessor\OrderRefundProcessorFactory;

class OrderRefundController extends BaseController
{
	public function refundsAction(Request $request,$targetType)
    {
        $conditions = $this->prepareRefundSearchConditions($request->query->all());
        
        $processor = $this->getOrderRefundProcessor($targetType);

        $conditions['targetType'] = $targetType;
        if (!empty($conditions['title'])){

            $targets = $processor->findByLikeTitle(trim($conditions['title']));
            $conditions['targetIds'] = ArrayToolkit::column($targets, 'id');
            if (count($conditions['targetIds']) == 0){
                return $this->render('TopxiaAdminBundle:OrderRefund:refunds.html.twig', array(
		                'refunds' => array(),
		                'users' => array(),
		                'orders' => array(),
		                'paginator' => new Paginator($request,0,20),
		                'layout' => $processor->getRefundLayout(),
		                'targetType' => $targetType
		            )
                );
            }              
        }

        $paginator = new Paginator(
            $this->get('request'),
            $this->getOrderService()->searchRefundCount($conditions),
            20
        );

        $refunds = $this->getOrderService()->searchRefunds(
            $conditions,
            'latest',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $userIds = array_merge(ArrayToolkit::column($refunds, 'userId'), ArrayToolkit::column($refunds, 'operator'));
        $users = $this->getUserService()->findUsersByIds($userIds);
        $orders = $this->getOrderService()->findOrdersByIds(ArrayToolkit::column($refunds, 'orderId'));

        return $this->render('TopxiaAdminBundle:OrderRefund:refunds.html.twig', array(
            'refunds' => $refunds,
            'users' => $users,
            'orders' => $orders,
            'paginator' => $paginator,
            'layout' => $processor->getRefundLayout(),
            'targetType' => $targetType
        ));
    }

    protected function prepareRefundSearchConditions($conditions)
    {
        $conditions = array_filter($conditions);

        if (!empty($conditions['orderSn'])) {
            $order = $this->getOrderService()->getOrderBySn($conditions['orderSn']);
            $conditions['orderId'] = $order ? $order['id'] : -1;
            unset($conditions['orderSn']);
        }

        if (!empty($conditions['nickname'])) {
            $user = $this->getUserService()->getUserByNickname($conditions['nickname']);
            $conditions['userId'] = $user ? $user['id'] : -1;
            unset($conditions['nickname']);
        }

        return $conditions;
    }

    public function cancelRefundAction(Request $request, $id)
    {
    	$order = $this->getOrderService()->getOrder($id);
    	$this->getOrderRefundProcessor($order["targetType"])->cancelRefundOrder($id);
        return $this->createJsonResponse(true);
    }

    public function auditRefundAction(Request $request, $id)
    {
        $order = $this->getOrderService()->getOrder($id);

        if ($request->getMethod() == 'POST') {
            $data = $request->request->all();

            $pass = $data['result'] == 'pass' ? true : false;

            $this->getOrderService()->auditRefundOrder($order['id'], $pass, $data['amount'], $data['note']);
            $orderRefundProcessor = $this->getOrderRefundProcessor($order["targetType"]);
            $orderRefundProcessor->auditRefundOrder($id, $pass, $data);

            if($order['targetType'] == 'course') {
                $this->sendAuditRefundNotification($orderRefundProcessor,$order, $data);
            } else {
                if ($pass) {
                    $this->getNotificationService()->notify($order['userId'],'default',"您的退款申请已通过管理员审核");
                }else{
                    $this->getNotificationService()->notify($order['userId'],'default',"您的退款申请因{$data['note']}未通过审核");
                }
            }

            return $this->createJsonResponse(true);
        }

        return $this->render('TopxiaAdminBundle:OrderRefund:refund-confirm-modal.html.twig', array(
            'order' => $order,
        ));

    }

    protected function sendAuditRefundNotification($orderRefundProcessor, $order, $data)
    {
        $target = $orderRefundProcessor->getTarget($order['targetId']);
        if (empty($target)) {
            return false;
        }

        if ($data['result'] == 'pass') {
            $message = $this->setting('refund.successNotification', '');
        } else {
            $message = $this->setting('refund.failedNotification', '');
        }

        if (empty($message)) {
            return false;
        }

        $targetUrl = $this->generateUrl($order["targetType"].'_show', array('id' => $order['targetId']));
        $variables = array(
            "item" => "<a href='{$targetUrl}'>{$target['title']}</a>",
            // "{$order['targetType']}" => "<a href='{$targetUrl}'>{$target['title']}</a>",
            "amount" => $data["amount"],
            "note" => $data["note"],
        );

        $message = StringToolkit::template($message, $variables);
        $this->getNotificationService()->notify($order['userId'], 'default', $message);
    }

    protected function getOrderRefundProcessor($targetType)
    {
    	return OrderRefundProcessorFactory::create($targetType);
    }

    protected function getOrderService()
    {
        return $this->getServiceKernel()->createService('Order.OrderService');
    }

    protected function getNotificationService()
    {
        return $this->getServiceKernel()->createService('User.NotificationService');
    }
}