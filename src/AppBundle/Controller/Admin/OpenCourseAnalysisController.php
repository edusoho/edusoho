<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

class OpenCourseAnalysisController extends BaseController
{
    public function indexAction(Request $request)
    {
        return $this->redirect($this->generateUrl('admin_opencourse_analysis_referer_summary_list', array('date-range' => 'week')));
    }

    public function summaryAction(Request $request)
    {
        $query = $request->query->all();
        $timeRange = $this->getTimeRange($query);
        $conditions = array_merge($timeRange, array('targetType' => 'openCourse'));

        //根据refererHost分组统计数据总数
        $refererlogDatas = $this->getRefererLogService()->analysisSummary($conditions);
        $analysisDataNames = json_encode(ArrayToolkit::column($refererlogDatas, 'refererName'));

        return $this->render('admin/open-course-analysis/referer/summary.html.twig', array(
            'dateRange' => $this->getDataInfo($timeRange),
            'refererlogAnalysisList' => $refererlogDatas,
            'refererlogAnalysisDatas' => json_encode($refererlogDatas),
            'analysisDataNames' => $analysisDataNames,
        ));
    }

    public function summaryListAction(Request $request)
    {
        $query = $request->query->all();
        $timeRange = $this->getTimeRange($query);

        list($refererlogDatas, $paginator) = $this->getRefererLogData($request, $timeRange, array('hitNum', 'DESC'));

        $targetIds = ArrayToolkit::column($refererlogDatas, 'targetId');
        $openCourses = $this->getOpenCourseService()->findCoursesByIds($targetIds);
        $openCourses = ArrayToolkit::index($openCourses, 'id');

        return $this->render('admin/open-course-analysis/referer/list.html.twig', array(
            'dateRange' => $this->getDataInfo($timeRange),
            'refererlogDatas' => $refererlogDatas,
            'openCourses' => $openCourses,
            'paginator' => $paginator,
        ));
    }

    public function detailAction(Request $request, $id)
    {
        $course = $this->getOpenCourseService()->getCourse($id);

        $timeRange = $this->getTimeRange($request->query->all());
        $conditions = array(
            'targetType' => 'openCourse',
            'targetId' => $id,
            'startTime' => $timeRange['startTime'],
            'endTime' => $timeRange['endTime'],
        );

        list($paginator, $refererloglist) = $this->getDetailList($conditions);

        return $this->render('admin/open-course-analysis/referer/detail.html.twig', array(
            'paginator' => $paginator,
            'refererloglist' => $refererloglist,
            'course' => $course,
        ));
    }

    public function detailGraphAction(Request $request, $id)
    {
        $timeRange = $this->getTimeRange($request->query->all());
        $conditions = array(
            'targetType' => 'openCourse',
            'targetId' => $id,
            'startTime' => $timeRange['startTime'],
            'endTime' => $timeRange['endTime'],
        );
        $refererlogsDetail = $this->getRefererLogService()->analysisSummary($conditions);
        $refererlogNames = json_encode(ArrayToolkit::column($refererlogsDetail, 'refererName'));

        return $this->render('admin/open-course-analysis/referer/detail-graph.html.twig', array(
            'refererlogsDetail' => $refererlogsDetail,
            'refererlogDetailDatas' => json_encode($refererlogsDetail),
            'refererlogNames' => $refererlogNames,
            'targetId' => $id,
        ));
    }

    public function detailListAction(Request $request, $id)
    {
        $timeRange = $this->getTimeRange($request->query->all());
        $conditions = array(
            'targetType' => 'openCourse',
            'targetId' => $id,
            'startTime' => $timeRange['startTime'],
            'endTime' => $timeRange['endTime'],
        );

        list($paginator, $refererloglist) = $this->getDetailList($conditions);

        return $this->render('admin/open-course-analysis/parts/referer-detail-list.html.twig', array(
            'paginator' => $paginator,
            'refererloglist' => $refererloglist,
            'targetId' => $id,
        ));
    }

    public function watchAction(Request $request)
    {
        $timeRange = $this->getTimeRange($request->query->all());
        $startTime = $timeRange['startTime'];
        $endTime = $timeRange['endTime'];
        $type = $request->query->get('type');

        $conditions = array(
            'startTime' => $startTime,
            'endTime' => $endTime,
            'targetType' => 'openCourse',
        );

        if (!empty($type)) {
            $conditions['targetInnerType'] = $type;
        }
        $totalWatchNum = $this->getRefererLogService()->countRefererLogs($conditions);
        $logs = $this->getRefererLogService()->searchRefererLogs($conditions, array('createdTime' => 'DESC'), 0, $totalWatchNum);
        $totalOpenCourseNum = count(array_unique(ArrayToolkit::column($logs, 'targetId')));
        $logsGroupByDate = $this->getRefererLogService()->findRefererLogsGroupByDate($conditions);
        $logsGroupByDate = $this->fillDateRangeWithLogsGroupDate($logsGroupByDate, $startTime, $endTime);

        $watchData = array(
            'date' => array_keys($logsGroupByDate),
            'watchNum' => array_values(array_map(function ($logs) {
                return count($logs);
            }, $logsGroupByDate)),
        );

        $averageWatchNum = empty($watchData['watchNum']) ? 0 : number_format(array_sum($watchData['watchNum']) / count($watchData['watchNum']));

        return $this->render('admin/open-course-analysis/referer/watch.html.twig', array(
            'dateRange' => $this->getDataInfo($timeRange),
            'totalOpenCourseNum' => $totalOpenCourseNum,
            'totalWatchNum' => $totalWatchNum,
            'watchData' => json_encode($watchData),
            'averageWatchNum' => $averageWatchNum,
        ));
    }

    /**
     * 补充日期不存在的记录.
     *
     * @param array $logsGroupByDate 通过日期分组的数据
     * @param int   $startTime       开始日期
     * @param int   $endTime         结束日期
     *
     * @return array 完善后的日期分组数据
     */
    protected function fillDateRangeWithLogsGroupDate($logsGroupByDate, $startTime, $endTime)
    {
        $begin = new \DateTime(date('Y-m-d', $startTime));
        $end = new \DateTime(date('Y-m-d', $endTime));
        $interval = new \DateInterval('P1D');
        $dateRange = new \DatePeriod($begin, $interval, $end);

        foreach ($dateRange as $date) {
            $key = $date->format('Y-m-d');
            if (!isset($logsGroupByDate[$key])) {
                $logsGroupByDate[$key] = array();
            }
        }

        uksort($logsGroupByDate, function ($a, $b) {
            return $a > $b;
        });

        return $logsGroupByDate;
    }

    private function getDetailList($conditions)
    {
        $paginator = new Paginator(
            $this->get('request'),
            $this->getRefererLogService()->countDistinctLogsByField($conditions, $field = 'refererUrl'),
            10
        );
        $refererloglist = $this->getRefererLogService()->searchAnalysisSummaryList(
            $conditions,
            $groupBy = 'refererUrl',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return array($paginator, $refererloglist);
    }

    protected function getDataInfo($timeRange)
    {
        return array(
            'startTime' => empty($timeRange['startTime']) ? '' : date('Y-m-d', $timeRange['startTime']),
            'endTime' => empty($timeRange['endTime']) ? '' : date('Y-m-d', $timeRange['endTime']),
            'yesterdayStart' => date('Y-m-d', strtotime(date('Y-m-d', time())) - 1 * 24 * 3600),
            'yesterdayEnd' => date('Y-m-d', strtotime(date('Y-m-d', time())) - 1 * 24 * 3600),

            'lastWeekStart' => date('Y-m-d', strtotime(date('Y-m-d', time())) - 6 * 24 * 3600),
            'lastWeekEnd' => date('Y-m-d', strtotime(date('Y-m-d', time()))),

            'lastMonthStart' => date('Y-m-d', strtotime(date('Y-m-d', time())) - 29 * 24 * 3600),
            'lastMonthEnd' => date('Y-m-d', strtotime(date('Y-m-d', time()))),
        );
    }

    protected function getTimeRange($fields)
    {
        if (isset($fields['startTime']) || isset($fields['endTime'])) {
            $timeRange = array(
                'startTime' => empty($fields['startTime']) ? null : strtotime($fields['startTime']),
                'endTime' => empty($fields['endTime']) ? null : (strtotime($fields['endTime'].'23:59:59')),
            );
        } else {
            $timeRange = array('startTime' => strtotime(date('Y-m-d', time())) - 7 * 24 * 3600, 'endTime' => strtotime(date('Y-m-d', time()).' 23:59:59'));
        }

        return $timeRange;
    }

    public function conversionAction(Request $request)
    {
        $timeRange = $this->getTimeRange($request->query->all());

        $conditions = array(
            'startTime' => $timeRange['startTime'],
            'endTime' => $timeRange['endTime'],
        );
        list($refererLogs, $paginator) = $this->getRefererLogData($request, $conditions, array('orderCount', 'DESC'));

        $courseIds = ArrayToolkit::column($refererLogs, 'targetId');
        $courses = ArrayToolkit::index($this->getOpenCourseService()->findCoursesByIds($courseIds), 'id');

        $totalData = $this->getTotalConversionData();

        return $this->render('admin/open-course-analysis/conversion/index.html.twig', array(
            'courses' => $courses,
            'paginator' => $paginator,
            'refererLogs' => $refererLogs,
            'dateRange' => $this->getDataInfo($timeRange),
            'totalData' => $totalData,
        ));
    }

    public function conversionResultAction(Request $request, $courseId)
    {
        $course = $this->getOpenCourseService()->getCourse($courseId);

        $timeRange = $this->getTimeRange($request->query->all());
        $conditions = array_merge($timeRange, array('sourceTargetId' => $courseId));

        $orderLogs = $this->getConversionOrderData($conditions);

        return $this->render('admin/open-course-analysis/conversion/result-modal.html.twig', array(
            'orderLogs' => $orderLogs,
            'course' => $course,
        ));
    }

    protected function getRefererLogData(Request $request, $conditions, $orderBy)
    {
        $conditions['targetType'] = 'openCourse';
        $startTime = ArrayToolkit::get($conditions, 'startTime', '');
        $endTime = ArrayToolkit::get($conditions, 'endTime', '');
        unset($conditions['startTime']);
        unset($conditions['endTime']);

        $paginator = new Paginator(
            $this->get('request'),
            $this->getRefererLogService()->countDistinctLogsByField($conditions, 'targetId'),
            10
        );

        $refererLogs = $this->getRefererLogService()->findRefererLogsGroupByTargetId(
            'openCourse',
            $orderBy,
            $startTime,
            $endTime,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return array($refererLogs, $paginator);
    }

    protected function getConversionOrderData($conditions)
    {
        $conditions['sourceTargetType'] = 'openCourse';

        $orderLogs = $this->getOrderRefererLogService()->searchOrderRefererLogs(
            $conditions,
            array('buyNum' => 'DESC'),
            0, 10,
            'targetId'
        );

        if (!$orderLogs) {
            return array();
        }

        foreach ($orderLogs as $key => $orderLog) {
            if ($orderLog['targetType'] == 'course') {
                $orderLogs[$key]['target'] = $this->getCourseTarget($orderLog['targetId']);
            } elseif ($orderLog['targetType'] == 'classroom') {
                $orderLogs[$key]['target'] = $this->getClassroomTarget($orderLog['targetId']);
            } elseif ($orderLog['targetType'] == 'vip') {
                $orderLogs[$key]['target'] = $this->getVipTarget($orderLog['targetId']);
            }

            $conditions['targetId'] = $orderLog['targetId'];

            $orderLogs[$key]['orderCount'] = $this->getOrderRefererLogService()->searchDistinctOrderRefererLogCount($conditions, 'orderId');
        }

        return $orderLogs;
    }

    protected function getCourseTarget($targetId)
    {
        $course = $this->getCourseService()->getCourse($targetId);
        $target = array();
        if ($course) {
            $target = array('title' => $course['title'], 'price' => $course['price']);
        }

        return $target;
    }

    protected function getClassroomTarget($targetId)
    {
        $classroom = $this->getClassroomService()->getClassroom($targetId);
        $target = array();
        if ($classroom) {
            $target = array('title' => $classroom['title'], 'price' => $classroom['price']);
        }

        return $target;
    }

    protected function getVipTarget($targetId)
    {
        $target = array();
        if ($this->isPluginInstalled('Vip')) {
            $vip = $this->getVipLevelService()->getLevel($targetId);
            if ($vip) {
                $target = array('title' => $vip['name'], 'price' => $vip['monthPrice']);
            }
        }

        return $target;
    }

    protected function getTotalConversionData()
    {
        $totalData = array();

        $totalData['visitCount'] = $this->getRefererLogService()->countRefererLogs(array('targetType' => 'openCourse'));
        $totalData['orderCount'] = $this->getOrderRefererLogService()->searchOrderRefererLogCount(array(), '');

        return $totalData;
    }

    protected function getOpenCourseService()
    {
        return $this->createService('OpenCourse:OpenCourseService');
    }

    protected function getRefererLogService()
    {
        return $this->createService('RefererLog:RefererLogService');
    }

    protected function getOrderRefererLogService()
    {
        return $this->createService('RefererLog:OrderRefererLogService');
    }

    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getVipLevelService()
    {
        return $this->createService('VipPlugin:Vip:LevelService');
    }
}
