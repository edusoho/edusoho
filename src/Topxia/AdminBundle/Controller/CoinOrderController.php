<?php

namespace Topxia\AdminBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\AdminBundle\Controller\BaseController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CoinOrderController extends BaseController
{
    public function ordersAction(Request $request)
    {
        $fields     = $request->query->all();
        $conditions = array();

        if (!empty($fields)) {
            $conditions = $fields;
        }

        if (isset($conditions['keywordType'])) {
            if ($conditions['keywordType'] == 'userName') {
                $conditions['keywordType'] = 'userId';
                $userFindbyNickName        = $this->getUserService()->getUserByNickname($conditions['keyword']);
                $conditions['keyword']     = $userFindbyNickName ? $userFindbyNickName['id'] : -1;
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
            $this->getCashOrdersService()->searchOrdersCount($conditions),
            20
        );

        $orders = $this->getCashOrdersService()->searchOrders(
            $conditions,
            array('createdTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        foreach ($orders as $index => $expiredOrderToBeUpdated) {
            if ((($expiredOrderToBeUpdated["createdTime"] + 48 * 60 * 60) < time()) && ($expiredOrderToBeUpdated["status"] == 'created')) {
                $this->getOrderService()->cancelOrder($expiredOrderToBeUpdated['id']);
                $orders[$index]['status'] = 'cancelled';
            }
        }

        $userIds = ArrayToolkit::column($orders, 'userId');
        $users   = $this->getUserService()->findUsersByIds($userIds);
        return $this->render('TopxiaAdminBundle:Coin:coin-orders.html.twig', array(
            'request'   => $request,
            'users'     => $users,
            'orders'    => $orders,
            'paginator' => $paginator
        ));
    }

    public function logsAction($id)
    {
        $order = $this->getCashOrdersService()->getOrder($id);
        $user  = $this->getUserService()->getUser($order['userId']);

        $orderLogs = $this->getCashOrdersService()->getLogsByOrderId($order['id']);

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($orderLogs, 'userId'));

        return $this->render('TopxiaAdminBundle:Coin:order-log-modal.html.twig', array(
            'order'     => $order,
            'user'      => $user,
            'orderLogs' => $orderLogs,
            'users'     => $users
        ));
    }

    public function exportCsvAction(Request $request) //coin

    {
        $conditions = $request->query->all();

        if (isset($conditions['keywordType'])) {
            if ($conditions['keywordType'] == 'userName') {
                $conditions['keywordType'] = 'userId';
                $userFindbyNickName        = $this->getUserService()->getUserByNickname($conditions['keyword']);
                $conditions['keyword']     = $userFindbyNickName ? $userFindbyNickName['id'] : -1;
            }
        }

        if (isset($conditions['keywordType'])) {
            $conditions[$conditions['keywordType']] = $conditions['keyword'];
            unset($conditions['keywordType']);
            unset($conditions['keyword']);
        }

        if (!empty($conditions['startTime']) && !empty($conditions['endTime'])) {
            $conditions['startTime'] = strtotime($conditions['startTime']);
            $conditions['endTime']   = strtotime($conditions['endTime']);
        }

        $status  = array('created' => '未付款', 'paid' => '已付款', 'cancelled' => '已关闭');
        $payment = $this->get('topxia.twig.web_extension')->getDict('payment');
        $orders  = $this->getCashOrdersService()->searchOrders($conditions, array('createdTime', 'DESC'), 0, PHP_INT_MAX);

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
            $member .= $orders['sn'] . ",";
            $member .= $status[$orders['status']] . ",";
            $member .= $orders['title'] . ",";
            $member .= $users[$orders['userId']]['nickname'] . ",";
            $member .= $profiles[$orders['userId']]['truename'] ? $profiles[$orders['userId']]['truename'] . "," : "-" . ",";
            $member .= $orders['amount'] . ",";

            $orderPayment = empty($orders['payment']) ? 'none' : $orders['payment'];
            $member .= $payment[$orderPayment].",";

            $member .= date('Y-n-d H:i:s', $orders['createdTime']) . ",";

            if ($orders['paidTime'] != 0) {
                $member .= date('Y-n-d H:i:s', $orders['paidTime']) . ",";
            } else {
                $member .= "-" . ",";
            }

            $results[] = $member;
        }

        $str .= implode("\r\n", $results);
        $str = chr(239) . chr(187) . chr(191) . $str;

        $filename = sprintf("coin-order-(%s).csv", date('Y-n-d'));

        $response = new Response();
        $response->headers->set('Content-type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $response->headers->set('Content-length', strlen($str));
        $response->setContent($str);

        return $response;
    }

    protected function getCashOrdersService()
    {
        return $this->getServiceKernel()->createService('Cash.CashOrdersService');
    }

    protected function getCashService()
    {
        return $this->getServiceKernel()->createService('Cash.CashService');
    }
}
