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
        $query      = $request->query->all();
        $timeRange  = $this->getTimeRange($query);
        $conditions = array_merge($timeRange, array('targetType' => 'openCourse'));

        $paginator = new Paginator(
            $this->get('request'),
            $this->getRefererLogService()->searchAnalysisSummaryListCount($conditions),
            20
        );
        $refererlogDatas = $this->getRefererLogService()->searchAnalysisSummaryList(
            $conditions,
            $groupBy = 'targetId',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

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
        $refererlogsDetail = $this->getRefererLogService()->searchAnalysisSummary($conditions);
        $refererlogNames   = json_encode(ArrayToolkit::column($refererlogsDetail, 'refererName'));

        list($paginator, $refererloglist) = $this->getDetailList($conditions);

        return $this->render("TopxiaAdminBundle:OpenCourseAnalysis/Referer:detail.html.twig", array(
            'refererlogsDetail'     => $refererlogsDetail,
            'refererlogDetailDatas' => json_encode($refererlogsDetail),
            'refererlogNames'       => $refererlogNames,
            'paginator'             => $paginator,
            'refererloglist'        => $refererloglist,
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

        if (!empty($request->query->get('type'))) {
            $conditions['targetInnerType'] = $request->query->get('type');
        }

        $logsGroupByDate = $this->getRefererLogService()->findRefererLogsGroupByDate($conditions);

        $watchData = array(
            'date'     => array_keys($logsGroupByDate),
            'watchNum' => array_values(array_map(function ($logs) {
                return count($logs);
            }, $logsGroupByDate))
        );

        $averageWatchNum = empty($watchData['watchNum']) ? 0 : array_sum($watchData['watchNum']) / count($watchData['watchNum']);

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
            $this->getRefererLogService()->searchAnalysisDetailListCount($conditions),
            20
        );
        $refererloglist = $this->getRefererLogService()->searchAnalysisDetailList(
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

    protected function getRefererLogService()
    {
        return $this->getServiceKernel()->createService('RefererLog.RefererLogService');
    }

    protected function getOpenCourseService()
    {
        return $this->getServiceKernel()->createService('OpenCourse.OpenCourseService');
    }
}
