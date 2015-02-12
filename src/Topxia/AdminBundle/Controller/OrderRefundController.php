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
		                'layout' => $processor->getLayout(),
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

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($refunds, 'userId'));
        $orders = $this->getOrderService()->findOrdersByIds(ArrayToolkit::column($refunds, 'orderId'));

        
        return $this->render('TopxiaAdminBundle:OrderRefund:refunds.html.twig', array(
            'refunds' => $refunds,
            'users' => $users,
            'orders' => $orders,
            'paginator' => $paginator,
            'layout' => $processor->getLayout(),
            'targetType' => $targetType
        ));
    }

    private function prepareRefundSearchConditions($conditions)
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

            $this->getOrderRefundProcessor($order["targetType"])->auditRefundOrder($id, $pass, $data);

            return $this->createJsonResponse(true);
        }

        return $this->render('TopxiaAdminBundle:OrderRefund:refund-confirm-modal.html.twig', array(
            'order' => $order,
        ));

    }

    protected function getOrderRefundProcessor($targetType)
    {
    	return OrderRefundProcessorFactory::create($targetType);
    }

    protected function getOrderService()
    {
        return $this->getServiceKernel()->createService('Order.OrderService');
    }
}