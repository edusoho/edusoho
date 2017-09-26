<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\MathToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Common\FileToolkit;
use AppBundle\Common\ArrayToolkit;
use Biz\Order\Service\OrderService;
use Codeages\Biz\Framework\Pay\Service\PayService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends BaseController
{
    private $statusMap = null;

    private $displayStatusDict = null;

    private $paymentDict = null;

    public function manageAction(Request $request)
    {
        $conditions = $request->query->all();

        $conditions = $this->prepareConditions($conditions);

        $paginator = new Paginator(
            $request,
            $this->getOrderService()->countOrders($conditions),
            20
        );

        $orders = $this->getOrderService()->searchOrders(
            $conditions,
            array('created_time' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $orderIds = ArrayToolkit::column($orders, 'id');
        $orderSns = ArrayToolkit::column($orders, 'sn');

        $orderItems = $this->getOrderService()->findOrderItemsByOrderIds($orderIds);
        $orderItems = ArrayToolkit::index($orderItems, 'order_id');

        $paymentTrades = $this->getPayService()->findTradesByOrderSns($orderSns);
        $paymentTrades = ArrayToolkit::index($paymentTrades, 'order_sn');

        foreach ($orders as &$order) {
            $order['item'] = empty($orderItems[$order['id']]) ? array() : $orderItems[$order['id']];
            $order['trade'] = empty($paymentTrades[$order['sn']]) ? array() : $paymentTrades[$order['sn']];
        }

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($orders, 'user_id'));

        return $this->render(
            'admin/order/list.html.twig',
            array(
                'request' => $request,
                'orders' => $orders,
                'users' => $users,
                'paginator' => $paginator,
            )
        );
    }

    protected function prepareConditions($conditions)
    {
        if (!empty($conditions['orderItemType'])) {
            $conditions['order_item_target_type'] = $conditions['orderItemType'];
        }

        if (isset($conditions['keywordType'])) {
            $conditions[$conditions['keywordType']] = trim($conditions['keyword']);
        }

        if (!empty($conditions['startDateTime'])) {
            $conditions['start_time'] = strtotime($conditions['startDateTime']);
        }

        if (!empty($conditions['endDateTime'])) {
            $conditions['end_time'] = strtotime($conditions['endDateTime']);
        }

        if (isset($conditions['buyer'])) {
            $user = $this->getUserService()->getUserByNickname($conditions['buyer']);
            $conditions['user_id'] = $user ? $user['id'] : -1;
        }

        if (!empty($conditions['displayStatus'])) {
            $conditions['in_status'] = $this->container->get('web.twig.order_extension')->getOrderStatusFromDisplayStatus($conditions['displayStatus'], 1);
        }

        return $conditions;
    }

    public function detailAction($id)
    {
        $order = $this->getOrderService()->getOrder($id);

        $user = $this->getUserService()->getUser($order['user_id']);

        $orderLogs = $this->getOrderService()->findOrderLogsByOrderId($order['id']);

        $orderItems = $this->getOrderService()->findOrderItemsByOrderId($order['id']);

        $paymentTrade = $this->getPayService()->getTradeByTradeSn($order['trade_sn']);

        $orderDeducts = $this->getOrderService()->findOrderItemDeductsByOrderId($order['id']);

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($orderLogs, 'user_id'));

        return $this->render('admin/order/detail.html.twig', array(
            'order' => $order,
            'user' => $user,
            'orderLogs' => $orderLogs,
            'orderItems' => $orderItems,
            'paymentTrade' => $paymentTrade,
            'orderDeducts' => $orderDeducts,
            'users' => $users,
        ));
    }

    /**
     *  导出订单.
     */
    public function exportCsvAction(Request $request)
    {
        $start = $request->query->get('start', 0);

        $magic = $this->setting('magic');
        $limit = $magic['export_limit'];

        $conditions = $this->buildExportCondition($request);

        $orderCount = $this->getOrderService()->countOrders($conditions);
        $orders = $this->getOrderService()->searchOrders($conditions, array('created_time' => 'DESC'), $start, $limit);

        $studentUserIds = ArrayToolkit::column($orders, 'user_id');

        $users = $this->getUserService()->findUsersByIds($studentUserIds);
        $users = ArrayToolkit::index($users, 'id');

        $profiles = $this->getUserService()->findUserProfilesByIds($studentUserIds);
        $profiles = ArrayToolkit::index($profiles, 'id');

        $str = '订单号,订单状态,订单名称,订单金额,优惠金额,实付总额,虚拟币支付,现金支付,支付方式,用户名,真实姓名,邮箱,联系电话,创建时间,付款时间';

        $str .= "\r\n";

        $results = array();

        $results = $this->generateExportData($orders, $users, $profiles, $results);

        $loop = $request->query->get('loop', 0);
        ++$loop;

        $enableRedirect = $loop * $limit < $orderCount; //当前已经读取的数据小于总数据,则继续跳转获取
        $readTempDate = $start;
        $file = $this->getExportFile($request);

        if ($enableRedirect) {
            $content = implode("\r\n", $results);
            file_put_contents($file, $content."\r\n", FILE_APPEND);

            return $this->redirect(
                $this->generateUrl(
                    'admin_order_manage_export_csv',
                    array('loop' => $loop, 'start' => $loop * $limit, 'fileName' => $file)
                )
            );
        } elseif ($readTempDate) {
            $str .= file_get_contents($file);
            FileToolkit::remove($file);
        }

        $str .= implode("\r\n", $results);
        $str = chr(239).chr(187).chr(191).$str;
        $filename = sprintf('order-(%s).csv', date('Y-n-d'));

        $response = new Response();
        $response->headers->set('Content-type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$filename.'"');
        $response->headers->set('Content-length', strlen($str));
        $response->setContent($str);

        return $response;
    }

    private function buildExportCondition($request)
    {
        $conditions = $request->query->all();

        $conditions = $this->prepareConditions($conditions);

        return $conditions;
    }

    private function getExportFile($request)
    {
        $user = $this->getUser();
        $fileName = $request->query->get('fileName', 'export_content_order'.$user['id'].time().'.txt');
        $rootPath = $this->getParameter('topxia.upload.private_directory');

        return $rootPath.DIRECTORY_SEPARATOR.$fileName;
    }

    private function generateExportData($orders, $users, $profiles, $results)
    {
        $str = '订单号,订单状态,订单名称,总价,优惠金额,实付总额,虚拟币支付,现金支付,支付方式,用户名,真实姓名,邮箱,联系电话,创建时间,付款时间';
        foreach ($orders as $key => $order) {
            $member = '';

            // 订单号
            $member .= $order['sn'].',';
            // 订单状态
            $member .= $this->getExportStatus($order['status']).',';

            //CSV会将字段里的两个双引号""显示成一个
            $order['title'] = str_replace('"', '""', $order['title']);
            // 订单名称
            $member .= '"'.$order['title'].'",';
            // 总价
            $member .= MathToolkit::simple($order['price_amount'], 0.01).',';
            // 优惠金额
            $member .= MathToolkit::simple($order['price_amount'] - $order['pay_amount'], 0.01).',';
            // 实付总额
            $member .= MathToolkit::simple($order['pay_amount'], 0.01).',';
            // 虚拟币支付
            $member .= MathToolkit::simple($order['paid_coin_amount'], 0.01).',';
            // 现金支付
            $member .= MathToolkit::simple($order['paid_cash_amount'], 0.01).',';
            // 支付方式
            $member .= $this->getExportPayment($order['payment']).',';

            //用户名
            $member .= $users[$order['user_id']]['nickname'].',';
            //真实姓名
            $member .= $profiles[$order['user_id']]['truename'] ? $profiles[$order['user_id']]['truename'].',' : '-'.',';
            //邮箱
            $member .= $users[$order['user_id']]['email'].',';
            //联系电话
            $member .= $users[$order['user_id']]['verifiedMobile'].',';
            //创建时间
            $member .= date('Y-n-d H:i:s', $order['created_time']).',';
            //付款时间
            if ($order['pay_time'] != 0) {
                $member .= date('Y-n-d H:i:s', $order['pay_time']);
            } else {
                $member .= '-';
            }

            $results[] = $member;
        }

        return $results;
    }

    private function getExportStatus($orderStatus)
    {
        if (!$this->statusMap) {
            $this->statusMap = $this->container->get('web.twig.order_extension')->getStatusMap();
        }

        if (!$this->displayStatusDict) {
            $this->displayStatusDict = $this->container->get('codeages_plugin.dict_twig_extension')->getDict('orderDisplayStatus');
        }

        return isset($this->statusMap[$orderStatus]) && isset($this->displayStatusDict[$this->statusMap[$orderStatus]]) ? $this->displayStatusDict[$this->statusMap[$orderStatus]] : $orderStatus;
    }

    private function getExportPayment($payment)
    {
        if (!$this->paymentDict) {
            $this->paymentDict = $this->get('codeages_plugin.dict_twig_extension')->getDict('newPayment');
        }

        return isset($this->paymentDict[$payment]) ? $this->paymentDict[$payment] : $payment;
    }

    /**
     * @return PayService
     */
    protected function getPayService()
    {
        return $this->createService('Pay:PayService');
    }

    /**
     * @return \Codeages\Biz\Framework\Order\Service\OrderService
     */
    protected function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }
}
