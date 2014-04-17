<?php
namespace Topxia\AdminBundle\Controller;

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

    public function manageAction(Request $request, $type, $layout)
    {
        $conditions = $request->query->all();
        $conditions['targetType'] = $type;

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

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($orders, 'userId'));

        return $this->render('TopxiaAdminBundle:Order:manage.html.twig', array(
            'request' => $request,
            'type' => $type,
            'layout' => $layout,
            'orders' => $orders ,
            'users' => $users,
            'paginator' => $paginator
        ));
    }

    public  function detailAction(Request $request, $id)
    {
        $order = $this->getOrderService()->getOrder($id);
        $user = $this->getUserService()->getUser($order['userId']);

        $orderLogs = $this->getOrderService()->findOrderLogs($order['id']);

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($orderLogs, 'userId'));
        
        return $this->render('TopxiaAdminBundle:Order:detail-modal.html.twig', array(
            'order'=>$order,
            'user'=>$user,
            'orderLogs'=>$orderLogs,
            'users' => $users
        ));
    }


    protected function getOrderService()
    {
        return $this->getServiceKernel()->createService('Order.OrderService');
    }
}