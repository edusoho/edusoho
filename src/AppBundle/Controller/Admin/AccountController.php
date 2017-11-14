<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Component\Echats\EchartsBuilder;

class AccountController extends BaseController
{
    public function IndexAction(Request $request)
    {
        $weekAndMonthDate = array(
            'weekDate' => $this->getStartTime('week'),
            'monthDate' => $this->getStartTime('month'),
            'seasonDate' => $this->getStartTime('quarter'),
            'yearDate' => $this->getStartTime('year'),
        );

        return $this->render('admin/operation-analysis/index.html.twig', array(
            'dates' => $weekAndMonthDate,
        ));
    }

    public function accountAnalysisAction(Request $request)
    {
        $weekTimeStart = $this->getStartTime('week');
        $weekTimeEnd = strtotime(date('Y-m-d', time()));
        $weekTotalPrice = $this->getAccountAnalysisData($weekTimeStart, $weekTimeEnd);

        $monthTimeStart = $this->getStartTime('month');
        $monthTimeEnd = strtotime(date('Y-m-d', time()));
        $monthTotalPrice = $this->getAccountAnalysisData($monthTimeStart, $monthTimeEnd);

        return $this->render('admin/operation-analysis/account-analysis-dashbord.html.twig', array(
            'weekTotalPrice' => $weekTotalPrice,
            'monthTotalPrice' => $monthTotalPrice,
        ));
    }

    private function getAccountAnalysisData($start, $endTime)
    {
        $totalPrice = $this->getOrderService()->analysisAmount(array('paidStartTime' => $start, 'paidEndTime' => $endTime, 'status' => 'paid'));
        $coinTotalPrice = $this->getOrderService()->analysisAmount(array('paidStartTime' => $start, 'paidEndTime' => $endTime, 'status' => 'paid', 'payment' => 'coin'));
        $cashTotalPrice = $totalPrice - $coinTotalPrice;
        $courseTotalPrice = $this->getOrderService()->analysisAmount(array('paidStartTime' => $start, 'paidEndTime' => $endTime, 'status' => 'paid', 'targetType' => 'course'));
        $classroomTotalPrice = $this->getOrderService()->analysisAmount(array('paidStartTime' => $start, 'paidEndTime' => $endTime, 'status' => 'paid', 'targetType' => 'classroom'));
        $vipTotalPrice = $this->getOrderService()->analysisAmount(array('paidStartTime' => $start, 'paidEndTime' => $endTime, 'status' => 'paid', 'targetType' => 'vip'));

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
        $monthTimeStart = $this->getStartTime('month');
        $monthTimeEnd = strtotime(date('Y-m-d', time()));

        $conditions = array(
            'targetType' => $type,
            'paidStartTime' => $monthTimeStart,
            'paidEndTime' => $monthTimeEnd,
            'status' => 'paid',
        );

        if ($type == 'all') {
            unset($conditions['targetType']);
        }

        $analysisAmounts = $this->getOrderService()->analysisAmountsDataByTitle(
            $conditions,
            array('count' => 'DESC'),
            0,
            10
        );

        foreach ($analysisAmounts as $key => $analysisAmount) {
            $analysisAmounts[$key]['title'] = preg_replace('/^购买/', '', $analysisAmount['title']);
            $analysisAmounts[$key]['title'] = preg_replace('/个月([\x{4e00}-\x{9fa5}])*/u', '个月', $analysisAmounts[$key]['title']);
        }

        return $this->render('admin/operation-analysis/account-analysis-rank-tr.html.twig', array(
            'analysisAmounts' => $analysisAmounts,
        ));
    }

    public function paymentRankAction(Request $request)
    {
        $type = $request->query->get('type');
        $monthTimeStart = $this->getStartTime('month');
        $monthTimeEnd = strtotime(date('Y-m-d', time()));

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

        $amounts = $this->getOrderService()->analysisAmountsDataByUserId(
            $conditions,
            array('count' => 'DESC'),
            0,
            10
        );

        $userIds = ArrayToolkit::column($amounts, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('admin/operation-analysis/account-analysis-payment-rank-table.html.twig', array(
            'amounts' => $amounts,
            'users' => $users,
        ));
    }

    public function accountStatisticAction(Request $request, $period)
    {
        $series = array();
        $startTime = $this->getStartTime($period);
        $endTime = time();
        $days = floor(($endTime - $startTime) / 3600 / 24);

        $conditions = array(
            'paidStartTime' => $startTime,
            'paidEndTime' => $endTime,
            'status' => 'paid',
            'payment' => 'coin',
        );

        $series['coinAmounts'] = $this->getOrderService()->analysisAmountsDataByTime(
            $conditions,
            array('count' => 'DESC'),
            0,
            10
        );

        unset($conditions['payment']);
        $conditions['cashPayment'] = 'coin';
        $series['cashAmounts'] = $this->getOrderService()->analysisAmountsDataByTime(
            $conditions,
            array('count' => 'DESC'),
            0,
            10
        );

        $amountAnalysis = EchartsBuilder::createLineDefaultData($days, 'Y/m/d', $series);

        return $this->createJsonResponse($amountAnalysis);
    }

    protected function getStartTime($period)
    {
        switch ($period) {
            case 'day':
                $startTime = strtotime(date('Y-m-d', time()));
                break;

            case 'week':
                $startTime = mktime(0, 0, 0, date('m'), date('d') - date('w') + 1, date('Y'));
                break;

            case 'month':
                $startTime = mktime(0, 0, 0, date('m'), 1, date('Y'));
                break;

            case 'quarter':
                $startTime = mktime(0, 0, 0, floor((date('m') - 1) / 3) * 3 + 1, 1, date('Y'));
                break;

            case 'year':
                $startTime = mktime(0, 0, 0, 1, 1, date('Y'));
                break;

            default:

                break;
        }

        return $startTime;
    }

    protected function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }

    protected function getAccountService()
    {
        return $this->createService('Pay:AccountService');
    }
}
