<?php

namespace Topxia\AdminBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

class AccountController extends BaseController
{
    public function IndexAction(Request $request)
    {
        $weekAndMonthDate = array('weekDate' => date('Y-m-d', time() - 6 * 24 * 60 * 60), 'monthDate' => date('Y-m-d', time() - 29 * 24 * 60 * 60),'seasonDate' => date('Y-m-d', time() - 90 * 24 * 60 * 60),'yearDate'=>date('Y-m-d', time() - 29 * 24 * 60 * 60),'seasonDate' => date('Y-m-d', time() - 365 * 24 * 60 * 60));

        return $this->render('TopxiaAdminBundle:AccountCenter:index.html.twig', array(
            'dates' => $weekAndMonthDate
        ));
    }

    public function accountAnalysisAction(Request $request)
    {
        $weekTimeStart = strtotime(date("Y-m-d", time()- 6 * 24 * 60 * 60));
        $weekTimeEnd   = strtotime(date("Y-m-d", time() + 24 * 3600));
        $weekTotalPrice = $this->getAccountAnalysisData($weekTimeStart, $weekTimeEnd);

        $monthTimeStart = strtotime(date("Y-m-d", time()- 30 * 24 * 60 * 60));
        $monthTimeEnd   = strtotime(date("Y-m-d", time() + 24 * 3600));
        $monthTotalPrice = $this->getAccountAnalysisData($monthTimeStart, $monthTimeEnd);

        
        return $this->render('TopxiaAdminBundle:AccountCenter:account-analysis-dashbord.html.twig', array(
            'weekTotalPrice' => $weekTotalPrice,
            'monthTotalPrice' => $monthTotalPrice,
        ));
    }

    private function getAccountAnalysisData($start, $endTime)
    {
        $totalPrice    = $this->getOrderService()->analysisAmount(array("paidStartTime" => $start, "paidEndTime" => $endTime, "status" => "paid"));
        $coinTotalPrice = $this->getOrderService()->analysisAmount(array("paidStartTime" => $start, "paidEndTime" => $endTime, "status" => "paid", 'payment'=>'coin'));
        $cashTotalPrice = $totalPrice - $coinTotalPrice;
        $courseTotalPrice = $this->getOrderService()->analysisAmount(array("paidStartTime" => $start, "paidEndTime" => $endTime, "status" => "paid", 'targetType' => 'course'));
        $classroomTotalPrice = $this->getOrderService()->analysisAmount(array("paidStartTime" => $start, "paidEndTime" => $endTime, "status" => "paid", 'targetType' => 'classroom'));
        $vipTotalPrice = $this->getOrderService()->analysisAmount(array("paidStartTime" => $start, "paidEndTime" => $endTime, "status" => "paid", 'targetType' => 'vip'));

        return array(
            'totalPrice' => $totalPrice,
            'coinTotalPrice' => $coinTotalPrice,
            'cashTotalPrice' => $cashTotalPrice,
            'courseTotalPrice' => $courseTotalPrice,
            'classroomTotalPrice' => $classroomTotalPrice,
            'vipTotalPrice' => $vipTotalPrice,
        );
    }

    public function rankAction(Request $request)
    {
        $type = $request->query->get('type');
        $monthTimeStart = strtotime(date("Y-m-d", time()- 30 * 24 * 60 * 60));
        $monthTimeEnd   = strtotime(date("Y-m-d", time() + 24 * 3600));
        $conditions = array(
            'targetType' => $type,
            'paidStartTime' => $monthTimeStart,
            'paidEndTime' => $monthTimeEnd,
            'status' => 'paid',
        );
        if ($type == 'all') {
            unset($conditions['targetType']);
        }
        $analysisAmounts = $this->getOrderService()->analysisAmountData('title', $conditions, array('count', 'DESC'), 0, 10);

        return $this->render('TopxiaAdminBundle:AccountCenter:account-analysis-rank-tr.html.twig', array(
            'analysisAmounts' => $analysisAmounts
        ));
    }

    public function paymentRankAction(Request $request)
    {
        $type = $request->query->get('type');
        $monthTimeStart = strtotime(date("Y-m-d", time()- 30 * 24 * 60 * 60));
        $monthTimeEnd   = strtotime(date("Y-m-d", time() + 24 * 3600));
        $conditions = array(
            'paidStartTime' => $monthTimeStart,
            'paidEndTime' => $monthTimeEnd,
            'status' => 'paid',
        );
        if ($type == 'cash') {
            $conditions['cashPayment'] = 'coin';
        } else {
            $conditions['payment'] = 'coin';
        }
        $amounts = $this->getOrderService()->analysisAmountData('userId', $conditions, array('count', 'DESC'), 0, 10);
        $userIds = ArrayToolkit::column($amounts, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('TopxiaAdminBundle:AccountCenter:account-analysis-payment-rank-table.html.twig', array(
            'amounts' => $amounts,
            'users' => $users
        ));
    }

    public function accountStatisticAction(Request $request, $period)
    {
        $series    = array();
        $days      = $this->getDaysDiff($period);
        $timeRange = $this->getTimeRange($period);


        $conditions              = array('paidStartTime' => $timeRange['startTime'], 'paidEndTime' => $timeRange['endTime'], 'status' => 'paid');
        $newOrders               = $this->getOrderService()->analysisOrderDate($conditions);
        $series['newOrderCount'] = $newOrders;

        $conditions['totalPriceGreaterThan'] = 0;
        $newPaidOrders                       = $this->getOrderService()->analysisOrderDate($conditions);
        $series['newPaidOrderCount']         = $newPaidOrders;

        $userAnalysis = EchartsBuilder::createLineDefaultData($days, 'Y/m/d', $series);
        return $this->createJsonResponse($userAnalysis);
    }

    private function getDaysDiff($period)
    {
        $days = $period == 'week' ? 6 : 29;
        return $days;
    }

    protected function getTimeRange($period)
    {
        $days = $this->getDaysDiff($period);

        return array('startTime' => strtotime(date('Y-m-d', time() - $days * 24 * 60 * 60)), 'endTime' => strtotime(date('Y-m-d', time() + 24 * 3600)));
    }

    protected function getOrderService()
    {
        return $this->getServiceKernel()->createService('Order.OrderService');
    }
}