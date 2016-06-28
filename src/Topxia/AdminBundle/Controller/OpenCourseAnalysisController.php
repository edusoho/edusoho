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
        $refererlogDatas = $this->getRefererLogService()->searchAnalysisSummary($conditions);
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
        $paginator  = new Paginator(
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
        array_walk($refererlogDatas, function ($referelog, $key) use (&$refererlogDatas) {
            $refererlogDatas[$key]['percent'] = empty($referelog['count']) ? '0%' : round($referelog['orderCount'] / $referelog['count'] * 100, 2).'%';
        });

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
        $refererlogsDetail = $this->getRefererLogService()->searchAnalysisDetail($conditions, $groupBy = 'refererHost');
        $refererlogsDetail = $this->prepareAnalysisDetailDatas($refererlogsDetail);
        $refererlogNames   = json_encode(ArrayToolkit::column($refererlogsDetail, 'refererHost'));

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

        $totalCount = array_sum(ArrayToolkit::column($refererloglist, 'count'));

        array_walk($refererloglist, function ($data, $key, $totalCount) use (&$refererloglist) {
            $refererloglist[$key]['percent']      = empty($totalCount) ? '0%' : round($data['count'] / $totalCount * 100, 2).'%';
            $refererloglist[$key]['orderPercent'] = empty($data['count']) ? '0%' : round($data['orderCount'] / $data['count'] * 100, 2).'%';
        }, $totalCount);

        return array($paginator, $refererloglist);
    }

    private function prepareCountPercent($refererlogDatas)
    {
        return $refererlogDatas;
    }

    private function prepareAnalysisDetailDatas($refererlogDatas)
    {
        if (empty($refererlogDatas)) {
            return array();
        }
        $lenght = 6;

        $analysisDatas      = array_slice($refererlogDatas, 0, $lenght);
        $otherAnalysisDatas = count($refererlogDatas) >= $lenght ? array_slice($refererlogDatas, $lenght) : array();

        $totalCount           = array_sum(ArrayToolkit::column($refererlogDatas, 'count'));
        $othertotalCount      = array_sum(ArrayToolkit::column($otherAnalysisDatas, 'count'));
        $otherOrdertotalCount = array_sum(ArrayToolkit::column($otherAnalysisDatas, 'orderCount'));

        array_push($analysisDatas, array('count' => $othertotalCount, 'orderCount' => $otherOrdertotalCount, 'refererHost' => '其他'));
        array_walk($analysisDatas, function ($data, $key, $totalCount) use (&$analysisDatas) {
            $analysisDatas[$key]['percent']      = empty($totalCount) ? '0%' : round($data['count'] / $totalCount * 100, 2).'%';
            $analysisDatas[$key]['orderPercent'] = empty($data['count']) ? '0%' : round($data['orderCount'] / $data['count'] * 100, 2).'%';
        }, $totalCount);

        return $analysisDatas;
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
