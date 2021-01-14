<?php

namespace AppBundle\Controller\AdminV2\Teach;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseService;
use Biz\OpenCourse\Service\OpenCourseService;
use Biz\RefererLog\Service\OrderRefererLogService;
use Biz\RefererLog\Service\RefererLogService;
use Symfony\Component\HttpFoundation\Request;
use VipPlugin\Biz\Vip\Service\LevelService;

class OpenCourseAnalysisController extends BaseController
{
    public function indexAction(Request $request)
    {
        return $this->redirect($this->generateUrl('admin_v2_open_course_analysis_referer_summary_list', ['date-range' => 'week']));
    }

    public function summaryAction(Request $request)
    {
        $query = $request->query->all();
        $timeRange = $this->getTimeRange($query);
        $conditions = array_merge($timeRange, ['targetType' => 'openCourse']);

        //根据refererHost分组统计数据总数
        $refererlogDatas = $this->getRefererLogService()->analysisSummary($conditions);
        $analysisDataNames = json_encode(ArrayToolkit::column($refererlogDatas, 'refererName'));

        return $this->render('admin-v2/teach/open-course-analysis/referer/summary.html.twig', [
            'dateRange' => $this->getDataInfo($timeRange),
            'refererlogAnalysisList' => $refererlogDatas,
            'refererlogAnalysisDatas' => json_encode($refererlogDatas),
            'analysisDataNames' => $analysisDataNames,
        ]);
    }

    public function summaryListAction(Request $request)
    {
        $query = $request->query->all();
        $timeRange = $this->getTimeRange($query);

        list($refererlogDatas, $paginator) = $this->getRefererLogData($request, $timeRange, ['hitNum', 'DESC']);

        $targetIds = ArrayToolkit::column($refererlogDatas, 'targetId');
        $openCourses = $this->getOpenCourseService()->findCoursesByIds($targetIds);
        $openCourses = ArrayToolkit::index($openCourses, 'id');

        return $this->render('admin-v2/teach/open-course-analysis/referer/list.html.twig', [
            'dateRange' => $this->getDataInfo($timeRange),
            'refererlogDatas' => $refererlogDatas,
            'openCourses' => $openCourses,
            'paginator' => $paginator,
        ]);
    }

    public function detailAction(Request $request, $id)
    {
        $course = $this->getOpenCourseService()->getCourse($id);

        $timeRange = $this->getTimeRange($request->query->all());
        $conditions = [
            'targetType' => 'openCourse',
            'targetId' => $id,
            'startTime' => $timeRange['startTime'],
            'endTime' => $timeRange['endTime'],
        ];

        list($paginator, $refererloglist) = $this->getDetailList($conditions);

        return $this->render('admin-v2/teach/open-course-analysis/referer/detail.html.twig', [
            'paginator' => $paginator,
            'refererloglist' => $refererloglist,
            'course' => $course,
        ]);
    }

    public function detailGraphAction(Request $request, $id)
    {
        $timeRange = $this->getTimeRange($request->query->all());
        $conditions = [
            'targetType' => 'openCourse',
            'targetId' => $id,
            'startTime' => $timeRange['startTime'],
            'endTime' => $timeRange['endTime'],
        ];
        $refererlogsDetail = $this->getRefererLogService()->analysisSummary($conditions);
        $refererlogNames = json_encode(ArrayToolkit::column($refererlogsDetail, 'refererName'));

        return $this->render('admin-v2/teach/open-course-analysis/referer/detail-graph.html.twig', [
            'refererlogsDetail' => $refererlogsDetail,
            'refererlogDetailDatas' => json_encode($refererlogsDetail),
            'refererlogNames' => $refererlogNames,
            'targetId' => $id,
        ]);
    }

    public function detailListAction(Request $request, $id)
    {
        $timeRange = $this->getTimeRange($request->query->all());
        $conditions = [
            'targetType' => 'openCourse',
            'targetId' => $id,
            'startTime' => $timeRange['startTime'],
            'endTime' => $timeRange['endTime'],
        ];

        list($paginator, $refererloglist) = $this->getDetailList($conditions);

        return $this->render('admin-v2/teach/open-course-analysis/parts/referer-detail-list.html.twig', [
            'paginator' => $paginator,
            'refererloglist' => $refererloglist,
            'targetId' => $id,
        ]);
    }

    public function watchAction(Request $request)
    {
        $timeRange = $this->getTimeRange($request->query->all());
        $startTime = $timeRange['startTime'];
        $endTime = $timeRange['endTime'];
        $type = $request->query->get('type');

        $conditions = [
            'startTime' => $startTime,
            'endTime' => $endTime,
            'targetType' => 'openCourse',
        ];

        if (!empty($type)) {
            $conditions['targetInnerType'] = $type;
        }
        $totalWatchNum = $this->getRefererLogService()->countRefererLogs($conditions);
        $logs = $this->getRefererLogService()->searchRefererLogs($conditions, ['createdTime' => 'DESC'], 0, $totalWatchNum);
        $totalOpenCourseNum = count(array_unique(ArrayToolkit::column($logs, 'targetId')));
        $logsGroupByDate = $this->getRefererLogService()->findRefererLogsGroupByDate($conditions);
        $logsGroupByDate = $this->fillDateRangeWithLogsGroupDate($logsGroupByDate, $startTime, $endTime);

        $watchData = [
            'date' => array_keys($logsGroupByDate),
            'watchNum' => array_values(array_map(function ($logs) {
                return count($logs);
            }, $logsGroupByDate)),
        ];

        $averageWatchNum = empty($watchData['watchNum']) ? 0 : number_format(array_sum($watchData['watchNum']) / count($watchData['watchNum']));

        return $this->render('admin-v2/teach/open-course-analysis/referer/watch.html.twig', [
            'dateRange' => $this->getDataInfo($timeRange),
            'totalOpenCourseNum' => $totalOpenCourseNum,
            'totalWatchNum' => $totalWatchNum,
            'watchData' => json_encode($watchData),
            'averageWatchNum' => $averageWatchNum,
        ]);
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
                $logsGroupByDate[$key] = [];
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

        return [$paginator, $refererloglist];
    }

    protected function getDataInfo($timeRange)
    {
        return [
            'startTime' => empty($timeRange['startTime']) ? '' : date('Y-m-d', $timeRange['startTime']),
            'endTime' => empty($timeRange['endTime']) ? '' : date('Y-m-d', $timeRange['endTime']),
            'yesterdayStart' => date('Y-m-d', strtotime(date('Y-m-d', time())) - 1 * 24 * 3600),
            'yesterdayEnd' => date('Y-m-d', strtotime(date('Y-m-d', time())) - 1 * 24 * 3600),

            'lastWeekStart' => date('Y-m-d', strtotime(date('Y-m-d', time())) - 6 * 24 * 3600),
            'lastWeekEnd' => date('Y-m-d', strtotime(date('Y-m-d', time()))),

            'lastMonthStart' => date('Y-m-d', strtotime(date('Y-m-d', time())) - 29 * 24 * 3600),
            'lastMonthEnd' => date('Y-m-d', strtotime(date('Y-m-d', time()))),
        ];
    }

    protected function getTimeRange($fields)
    {
        if (isset($fields['startTime']) || isset($fields['endTime'])) {
            $timeRange = [
                'startTime' => empty($fields['startTime']) ? null : strtotime($fields['startTime']),
                'endTime' => empty($fields['endTime']) ? null : (strtotime($fields['endTime'].'23:59:59')),
            ];
        } else {
            $timeRange = ['startTime' => strtotime(date('Y-m-d', time())) - 7 * 24 * 3600, 'endTime' => strtotime(date('Y-m-d', time()).' 23:59:59')];
        }

        return $timeRange;
    }

    public function conversionAction(Request $request)
    {
        $timeRange = $this->getTimeRange($request->query->all());

        $conditions = [
            'startTime' => $timeRange['startTime'],
            'endTime' => $timeRange['endTime'],
        ];
        list($refererLogs, $paginator) = $this->getRefererLogData($request, $conditions, ['orderCount', 'DESC']);

        $courseIds = ArrayToolkit::column($refererLogs, 'targetId');
        $courses = ArrayToolkit::index($this->getOpenCourseService()->findCoursesByIds($courseIds), 'id');

        $totalData = $this->getTotalConversionData();

        return $this->render('admin-v2/teach/open-course-analysis/conversion/index.html.twig', [
            'courses' => $courses,
            'paginator' => $paginator,
            'refererLogs' => $refererLogs,
            'dateRange' => $this->getDataInfo($timeRange),
            'totalData' => $totalData,
        ]);
    }

    public function conversionResultAction(Request $request, $courseId)
    {
        $course = $this->getOpenCourseService()->getCourse($courseId);

        $timeRange = $this->getTimeRange($request->query->all());
        $conditions = array_merge($timeRange, ['sourceTargetId' => $courseId]);

        $orderLogs = $this->getConversionOrderData($conditions);

        return $this->render('admin-v2/teach/open-course-analysis/conversion/result-modal.html.twig', [
            'orderLogs' => $orderLogs,
            'course' => $course,
        ]);
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

        return [$refererLogs, $paginator];
    }

    protected function getConversionOrderData($conditions)
    {
        $conditions['sourceTargetType'] = 'openCourse';

        $orderLogs = $this->getOrderRefererLogService()->searchOrderRefererLogs(
            $conditions,
            ['buyNum' => 'DESC'],
            0, 10
        );

        if (!$orderLogs) {
            return [];
        }

        foreach ($orderLogs as $key => $orderLog) {
            if ('course' == $orderLog['targetType']) {
                $orderLogs[$key]['target'] = $this->getCourseTarget($orderLog['targetId']);
            } elseif ('classroom' == $orderLog['targetType']) {
                $orderLogs[$key]['target'] = $this->getClassroomTarget($orderLog['targetId']);
            } elseif ('vip' == $orderLog['targetType']) {
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
        $target = [];
        if ($course) {
            $target = ['title' => $course['title'], 'price' => $course['price']];
        }

        return $target;
    }

    protected function getClassroomTarget($targetId)
    {
        $classroom = $this->getClassroomService()->getClassroom($targetId);
        $target = [];
        if ($classroom) {
            $target = ['title' => $classroom['title'], 'price' => $classroom['price']];
        }

        return $target;
    }

    protected function getVipTarget($targetId)
    {
        $target = [];
        if ($this->isPluginInstalled('Vip')) {
            $vip = $this->getVipLevelService()->getLevel($targetId);
            if ($vip) {
                $target = ['title' => $vip['name'], 'price' => $vip['monthPrice']];
            }
        }

        return $target;
    }

    protected function getTotalConversionData()
    {
        $totalData = [];

        $totalData['visitCount'] = $this->getRefererLogService()->countRefererLogs(['targetType' => 'openCourse']);
        $totalData['orderCount'] = $this->getOrderRefererLogService()->searchOrderRefererLogCount([], '');

        return $totalData;
    }

    /**
     * @return OpenCourseService
     */
    protected function getOpenCourseService()
    {
        return $this->createService('OpenCourse:OpenCourseService');
    }

    /**
     * @return RefererLogService
     */
    protected function getRefererLogService()
    {
        return $this->createService('RefererLog:RefererLogService');
    }

    /**
     * @return OrderRefererLogService
     */
    protected function getOrderRefererLogService()
    {
        return $this->createService('RefererLog:OrderRefererLogService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return LevelService
     */
    protected function getVipLevelService()
    {
        return $this->createService('VipPlugin:Vip:LevelService');
    }
}
