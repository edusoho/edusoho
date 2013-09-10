<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class CourseOrderController extends BaseController
{

    public function manageAction(Request $request)
    {
        $conditions = $request->query->all();

        $paginator = new Paginator(
            $this->get('request'),
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

        return $this->render('TopxiaAdminBundle:CourseOrder:index.html.twig', array(
            'orders' => $orders ,
            'users' => $users,
            'paginator' => $paginator
        ));

    }

    public  function detailAction(Request $request, $id)
    {
        $order = $this->getOrderService()->getOrder($id);
        $user = $this->getUserService()->getuser($order['userId']);
        $course = $this->getCourseService()->getCourse($order['courseId']);

        $orderLogs = $this->getOrderService()->findOrderLogs($order['id']);

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($orderLogs, 'userId'));
        
        return $this->render('TopxiaAdminBundle:CourseOrder:detail-modal.html.twig', array(
            'order'=>$order,
            'user'=>$user,
            'course'=>$course,
            'orderLogs'=>$orderLogs,
            'users' => $users
        ));
    }

    protected function getOrderService()
    {
        return $this->getServiceKernel()->createService('Course.OrderService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}