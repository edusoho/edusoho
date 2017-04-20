<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\Paginator;
use AppBundle\Common\FileToolkit;
use AppBundle\Common\ArrayToolkit;
use Biz\Order\Service\OrderService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends BaseController
{
    public function indexAction(Request $request)
    {
        return $this->render('admin/order/index.html.twig', array());
    }

    public function manageAction(Request $request, $targetType)
    {
        $conditions = $request->query->all();

        $conditions['targetType'] = $targetType;

        if (isset($conditions['keywordType'])) {
            $conditions[$conditions['keywordType']] = trim($conditions['keyword']);
        }
        $conditions = $this->prepareConditions($conditions);

        if (!empty($conditions['startDateTime']) && !empty($conditions['endDateTime'])) {
            $conditions['startTime'] = strtotime($conditions['startDateTime']);
            $conditions['endTime'] = strtotime($conditions['endDateTime']);
        }

        $paginator = new Paginator(
            $request,
            $this->getOrderService()->countOrders($conditions),
            20
        );
        $orders = $this->getOrderService()->searchOrders(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($orders, 'userId'));

        foreach ($orders as $index => $expiredOrderToBeUpdated) {
            if ((($expiredOrderToBeUpdated['createdTime'] + 48 * 60 * 60) < time(
                    )) && ($expiredOrderToBeUpdated['status'] == 'created')
            ) {
                $this->getOrderService()->cancelOrder($expiredOrderToBeUpdated['id']);
                $orders[$index]['status'] = 'cancelled';
            }
        }

        return $this->render(
            'admin/order/manage.html.twig',
            array(
                'request' => $request,
                'targetType' => $targetType,
                'orders' => $orders,
                'users' => $users,
                'paginator' => $paginator,
            )
        );
    }

    protected function prepareConditions($conditions)
    {
        if ($conditions['targetType'] != 'course') {
            return $conditions;
        }
        if (isset($conditions['courseSetTitle'])) {
            $conditions['title'] = $conditions['courseSetTitle'];
        }

        if (!empty($conditions['courseSetId'])) {
            $courses = $this->getCourseService()->findCoursesByCourseSetId($conditions['courseSetId']);
            $courseIds = ArrayToolkit::column($courses, 'courseSetId');
            $conditions['targetIds'] = empty($courseIds) ? array(-1) : $courseIds;
            unset($conditions['targetId']);
        }

        return $conditions;
    }

    public function detailAction(Request $request, $id)
    {
        return $this->forward(
            'AppBundle:Order:detail',
            array(
                'id' => $id,
            )
        );
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

        return $this->render(
            'admin/course-order/refund-confirm-modal.html.twig',
            array(
                'order' => $order,
            )
        );
    }

    /**
     *  导出订单.
     *
     * @param string $targetType classroom | course | vip
     */
    public function exportCsvAction(Request $request, $targetType)
    {
        $start = $request->query->get('start', 0);

        $magic = $this->setting('magic');
        $limit = $magic['export_limit'];

        $conditions = $this->buildExportCondition($request, $targetType);

        $status = array(
            'created' => '未付款',
            'paid' => '已付款',
            'refunding' => '退款中',
            'refunded' => '已退款',
            'cancelled' => '已关闭',
        );

        $payment = $this->get('codeages_plugin.dict_twig_extension')->getDict('payment');
        $orderCount = $this->getOrderService()->countOrders($conditions);
        $orders = $this->getOrderService()->searchOrders($conditions, array('createdTime' => 'DESC'), $start, $limit);
        $studentUserIds = ArrayToolkit::column($orders, 'userId');

        $users = $this->getUserService()->findUsersByIds($studentUserIds);
        $users = ArrayToolkit::index($users, 'id');

        $profiles = $this->getUserService()->findUserProfilesByIds($studentUserIds);
        $profiles = ArrayToolkit::index($profiles, 'id');

        if ($targetType == 'vip') {
            $str = '订单号,订单状态,订单名称,购买者,姓名,实付价格,支付方式,创建时间,付款时间';
        } else {
            $str = '订单号,订单状态,订单名称,订单价格,优惠码,优惠金额,虚拟币支付,实付价格,支付方式,购买者,姓名,操作,创建时间,付款时间';
        }

        $str .= "\r\n";

        $results = array();

        if ($targetType == 'vip') {
            $results = $this->generateVipExportData($orders, $status, $users, $profiles, $payment, $results);
        } else {
            $results = $this->generateExportData($orders, $status, $payment, $users, $profiles, $results);
        }

        $loop = $request->query->get('loop', 0);
        ++$loop;

        $enableRedirect = $loop * $limit < $orderCount; //当前已经读取的数据小于总数据,则继续跳转获取
        $readTempDate = $start;
        $file = $request->query->get('fileName', $this->genereateExportCsvFileName($targetType));

        if ($enableRedirect) {
            $content = implode("\r\n", $results);
            file_put_contents($file, $content."\r\n", FILE_APPEND);

            return $this->redirect(
                $this->generateUrl(
                    'admin_order_manage_export_csv',
                    array('targetType' => $targetType, 'loop' => $loop, 'start' => $loop * $limit, 'fileName' => $file)
                )
            );
        } elseif ($readTempDate) {
            $str .= file_get_contents($file);
            FileToolkit::remove($file);
        }

        $str .= implode("\r\n", $results);
        $str = chr(239).chr(187).chr(191).$str;
        $filename = sprintf('%s-order-(%s).csv', $targetType, date('Y-n-d'));

        $response = new Response();
        $response->headers->set('Content-type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$filename.'"');
        $response->headers->set('Content-length', strlen($str));
        $response->setContent($str);

        return $response;
    }

    private function buildExportCondition($request, $targetType)
    {
        $conditions = $request->query->all();

        if (!empty($conditions['startTime']) && !empty($conditions['endTime'])) {
            $conditions['startTime'] = strtotime($conditions['startTime']);
            $conditions['endTime'] = strtotime($conditions['endTime']);
        }

        $conditions['targetType'] = $targetType;

        return $conditions;
    }

    private function genereateExportCsvFileName($targetType)
    {
        $rootPath = $this->getParameter('topxia.upload.private_directory');
        $user = $this->getUser();

        return $rootPath.'/export_content'.$targetType.$user['id'].time().'.txt';
    }

    protected function sendAuditRefundNotification($order, $pass, $amount, $note)
    {
        $classroom = $this->getClassroomService()->getClassroom($order['targetId']);

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

    /**
     * @return OrderService
     */
    protected function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }

    private function generateVipExportData($orders, $status, $users, $profiles, $payment, $results)
    {
        foreach ($orders as $key => $order) {
            $member = '';
            $member .= $order['sn'].',';
            $member .= $status[$order['status']].',';
            $member .= $order['title'].',';
            $member .= $users[$order['userId']]['nickname'].',';
            $member .= $profiles[$order['userId']]['truename'] ? $profiles[$order['userId']]['truename'].',' : '-'.',';
            $member .= $order['amount'].',';
            $member .= $payment[$order['payment']].',';
            $member .= date('Y-n-d H:i:s', $order['createdTime']).',';

            if ($order['paidTime'] != 0) {
                $member .= date('Y-n-d H:i:s', $order['paidTime']).',';
            } else {
                $member .= '-'.',';
            }

            $results[] = $member;
        }

        return $results;
    }

    private function generateExportData($orders, $status, $payment, $users, $profiles, $results)
    {
        foreach ($orders as $key => $order) {
            $member = '';
            $member .= $order['sn'].',';
            $member .= $status[$order['status']].',';
            $member .= $order['title'].',';

            $member .= $order['totalPrice'].',';

            if (!empty($order['coupon'])) {
                $member .= $order['coupon'].',';
            } else {
                $member .= '无'.',';
            }

            $member .= $order['couponDiscount'].',';
            $member .= $order['coinRate'] ? ($order['coinAmount'] / $order['coinRate']).',' : '0,';
            $member .= $order['amount'].',';

            $orderPayment = empty($order['payment']) ? 'none' : $order['payment'];
            $member .= $payment[$orderPayment].',';

            $member .= $users[$order['userId']]['nickname'].',';
            $member .= $profiles[$order['userId']]['truename'] ? $profiles[$order['userId']]['truename'].',' : '-'.',';

            if (preg_match('/管理员添加/', $order['title'])) {
                $member .= '管理员添加,';
            } else {
                $member .= '-,';
            }

            $member .= date('Y-n-d H:i:s', $order['createdTime']).',';

            if ($order['paidTime'] != 0) {
                $member .= date('Y-n-d H:i:s', $order['paidTime']);
            } else {
                $member .= '-';
            }

            $results[] = $member;
        }

        return $results;
    }

    protected function getUserFieldService()
    {
        return $this->createService('User:UserFieldService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return \Biz\Course\Service\CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    protected function getCashService()
    {
        return $this->createService('Cash:CashService');
    }

    protected function getCashOrdersService()
    {
        return $this->createService('Cash:CashOrdersService');
    }
}
