<?php
namespace Topxia\AdminBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

class OpenCourseAnalysisController extends BaseController
{
    public function indexAction(Request $request)
    {
        return $this->redirect($this->generateUrl('admin_opencourse_analysis_referer_summary'));
    }

    public function summaryAction(Request $request)
    {
        $query      = $request->query->all();
        $timeRange  = $this->getTimeRange($query);
        $conditions = array_merge($timeRange, array('targetType' => 'openCourse'));

        //根据refererHost分组统计数据总数
        $refererlogDatas   = $this->getRefererLogService()->searchAnalysisSummary($conditions);
        $analysisDataNames = json_encode(ArrayToolkit::column($refererlogDatas, 'refererName'));
        return $this->render('TopxiaAdminBundle:OpenCourseAnalysis/Referer:index.html.twig', array(
            'dateRange'               => $this->getDataInfo($timeRange),
            'refererlogAnalysisList'  => $refererlogDatas,
            'refererlogAnalysisDatas' => json_encode($refererlogDatas),
            'analysisDataNames'       => $analysisDataNames
        ));
    }

    public function summaryListAction(Request $request)
    {
        $query     = $request->query->all();
        $timeRange = $this->getTimeRange($query);

        list($refererlogDatas, $paginator) = $this->getRefererLogData($request, $timeRange, array('hitNum', 'DESC'));

        $targetIds   = ArrayToolkit::column($refererlogDatas, 'targetId');
        $openCourses = $this->getOpenCourseService()->findCoursesByIds($targetIds);
        $openCourses = ArrayToolkit::index($openCourses, 'id');

        return $this->render('TopxiaAdminBundle:OpenCourseAnalysis/Referer:list.html.twig', array(
            'dateRange'       => $this->getDataInfo($timeRange),
            'refererlogDatas' => $refererlogDatas,
            'openCourses'     => $openCourses,
            'paginator'       => $paginator
        ));
    }

    public function detailAction(Request $request, $id)
    {
        $timeRange  = $this->getTimeRange($request->query->all());
        $conditions = array(
            'targetType' => 'openCourse',
            'targetId'   => $id,
            'startTime'  => $timeRange['startTime'],
            'endTime'    => $timeRange['endTime']
        );

        list($paginator, $refererloglist) = $this->getDetailList($conditions);

        return $this->render("TopxiaAdminBundle:OpenCourseAnalysis/Referer:detail.html.twig", array(
            'paginator'      => $paginator,
            'refererloglist' => $refererloglist,
            'targetId'       => $id
        ));
    }

    public function detailGraphAction(Request $request, $id)
    {
        $timeRange  = $this->getTimeRange($request->query->all());
        $conditions = array(
            'targetType' => 'openCourse',
            'targetId'   => $id,
            'startTime'  => $timeRange['startTime'],
            'endTime'    => $timeRange['endTime']
        );
        $refererlogsDetail = $this->getRefererLogService()->searchAnalysisSummary($conditions);
        $refererlogNames   = json_encode(ArrayToolkit::column($refererlogsDetail, 'refererName'));

        return $this->render("TopxiaAdminBundle:OpenCourseAnalysis/Referer:detail-graph.html.twig", array(
            'refererlogsDetail'     => $refererlogsDetail,
            'refererlogDetailDatas' => json_encode($refererlogsDetail),
            'refererlogNames'       => $refererlogNames,
            'targetId'              => $id
        ));
    }

    public function detailListAction(Request $request, $id)
    {
        $timeRange  = $this->getTimeRange($request->query->all());
        $conditions = array(
            'targetType' => 'openCourse',
            'targetId'   => $id,
            'startTime'  => $timeRange['startTime'],
            'endTime'    => $timeRange['endTime']
        );

        list($paginator, $refererloglist) = $this->getDetailList($conditions);

        return $this->render("TopxiaAdminBundle:OpenCourseAnalysis/Parts:referer-detail-list.html.twig", array(
            'paginator'      => $paginator,
            'refererloglist' => $refererloglist,
            'targetId'       => $id
        ));
    }

    public function watchAction(Request $request)
    {
        $timeRange          = $this->getTimeRange($request->query->all());
        $startTime          = $timeRange['startTime'];
        $endTime            = $timeRange['endTime'];
        $totalOpenCourseNum = $this->getOpenCourseService()->searchCourseCount(array(
            'status' => 'published'
        ));

        $totalWatchNum = $this->getRefererLogService()->searchRefererLogCount(array(
            'targetType' => 'openCourse'
        ));

        $conditions = array(
            'startTime'  => $startTime,
            'endTime'    => $endTime,
            'targetType' => 'openCourse'
        );

        $type = $request->query->get('type');
        if (!empty($type)) {
            $conditions['targetInnerType'] = $type;
        }

        $logsGroupByDate = $this->getRefererLogService()->findRefererLogsGroupByDate($conditions);

        $watchData = array(
            'date'     => array_keys($logsGroupByDate),
            'watchNum' => array_values(array_map(function ($logs) {
                return count($logs);
            }, $logsGroupByDate))
        );

        $averageWatchNum = empty($watchData['watchNum']) ? 0 : number_format(array_sum($watchData['watchNum']) / count($watchData['watchNum']));

        return $this->render("TopxiaAdminBundle:OpenCourseAnalysis/Referer:watch.html.twig", array(
            'dateRange'          => $this->getDataInfo($timeRange),
            'totalOpenCourseNum' => $totalOpenCourseNum,
            'totalWatchNum'      => $totalWatchNum,
            'watchData'          => json_encode($watchData),
            'averageWatchNum'    => $averageWatchNum
        ));
    }

    private function getDetailList($conditions)
    {
        $paginator = new Paginator(
            $this->get('request'),
            $this->getRefererLogService()->searchAnalysisSummaryListCount($conditions, $field = 'refererUrl'),
            20
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
            'startTime'      => date("Y-m-d", $timeRange['startTime']),
            'endTime'        => date("Y-m-d", $timeRange['endTime']),
            'yesterdayStart' => date("Y-m-d", strtotime(date("Y-m-d", time())) - 1 * 24 * 3600),
            'yesterdayEnd'   => date("Y-m-d", strtotime(date("Y-m-d", time()))),

            'lastWeekStart'  => date("Y-m-d", strtotime(date("Y-m-d", time())) - 7 * 24 * 3600),
            'lastWeekEnd'    => date("Y-m-d", strtotime(date("Y-m-d", time()))),

            'lastMonthStart' => date("Y-m-d", strtotime(date("Y-m-d", time())) - 30 * 24 * 3600),
            'lastMonthEnd'   => date("Y-m-d", strtotime(date("Y-m-d", time())))
        );
    }

    protected function getTimeRange($fields)
    {
        if (empty($fields['startTime']) && empty($fields['endTime'])) {
            return array('startTime' => strtotime(date("Y-m-d", time())) - 7 * 24 * 3600, 'endTime' => strtotime(date("Y-m-d", time())));
        }
        return array('startTime' => strtotime($fields['startTime']), 'endTime' => (strtotime($fields['endTime'])));
    }

    public function conversionAction(Request $request)
    {
        $timeRange = $this->getTimeRange($request->query->all());

        $conditions = array(
            'startTime' => $timeRange['startTime'],
            'endTime'   => $timeRange['endTime']
        );
        list($refererLogs, $paginator) = $this->getRefererLogData($request, $conditions, array('orderCount', 'DESC'));

        $courseIds = ArrayToolkit::column($refererLogs, 'targetId');
        $courses   = ArrayToolkit::index($this->getOpenCourseService()->findCoursesByIds($courseIds), 'id');

        $totalData = $this->getTotalConversionData();

        return $this->render('TopxiaAdminBundle:OpenCourseAnalysis/Conversion:index.html.twig', array(
            'courses'     => $courses,
            'paginator'   => $paginator,
            'refererLogs' => $refererLogs,
            'dateRange'   => $this->getDataInfo($timeRange),
            'totalData'   => $totalData
        ));
    }

    public function conversionResultAction(Request $request, $courseId)
    {
        $timeRange  = $this->getTimeRange($request->query->all());
        $conditions = array_merge($timeRange, array('sourceTargetId' => $courseId));

        $orderLogs = $this->getConversionOrderData($conditions);

        return $this->render('TopxiaAdminBundle:OpenCourseAnalysis/Conversion:result-modal.html.twig', array(
            'orderLogs' => $orderLogs,
            'dateRange' => $this->getDataInfo($timeRange),
            'targetId'  => $courseId
        ));
    }

    protected function getRefererLogData(Request $request, $conditions, $orderBy)
    {
        $conditions['targetType'] = 'openCourse';
        $startTime                = $conditions['startTime'];
        $endTime                  = $conditions['endTime'];
        unset($conditions['startTime']);
        unset($conditions['endTime']);

        $paginator = new Paginator(
            $this->get('request'),
            $this->getRefererLogService()->searchAnalysisSummaryListCount($conditions, 'targetId'),
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
            array('buyNum', 'DESC'),
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
                $orderLogs[$key]['target'] = $this->getVipTargt($orderLog['targetId']);
            }
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
        $target    = array();
        if ($classroom) {
            $target = array('title' => $classroom['title'], 'price' => $classroom['price']);
        }

        return $target;
    }

    protected function getVipTargt($targetId)
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

        $totalData['visitCount'] = $this->getRefererLogService()->searchRefererLogCount(array('targetType' => 'openCourse'));
        $totalData['orderCount'] = $this->getOrderRefererLogService()->searchOrderRefererLogCount(array(), '');

        return $totalData;
    }

    protected function getOpenCourseService()
    {
        return $this->getServiceKernel()->createService('OpenCourse.OpenCourseService');
    }

    protected function getRefererLogService()
    {
        return $this->getServiceKernel()->createService('RefererLog.RefererLogService');
    }

    protected function getOrderRefererLogService()
    {
        return $this->getServiceKernel()->createService('RefererLog.OrderRefererLogService');
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getVipLevelService()
    {
        return $this->getServiceKernel()->createService('Vip:Vip.LevelService');
    }
}
