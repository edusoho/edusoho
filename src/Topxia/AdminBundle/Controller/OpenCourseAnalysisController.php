<?php
namespace Topxia\AdminBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

class OpenCourseAnalysisController extends BaseController
{
    public function indexAction(Request $request)
    {
        return $this->redirect($this->generateUrl('admin_opencourse_analysis_referer'));
    }

    public function refererAction(Request $request)
    {
        $query      = $request->query->all();
        $timeRange  = $this->getTimeRange($query);
        $conditions = array_merge($timeRange, array('targetType' => 'openCourse'));

        //根据refererHost分组统计数据总数
        $refererlogDatas        = $this->getRefererLogService()->searchAnalysisRefererLogSum($conditions, $groupBy = 'refererHost');
        $refererlogAnalysisList = $this->prepareAnalysisDatas($refererlogDatas);
        $analysisDataNames      = json_encode(ArrayToolkit::column($refererlogAnalysisList, 'name'));
        return $this->render('TopxiaAdminBundle:OpenCourseAnalysis/Referer:index.html.twig', array(
            'dateRange'               => $this->getDataInfo($timeRange),
            'refererlogAnalysisList'  => $refererlogAnalysisList,
            'refererlogAnalysisDatas' => json_encode($refererlogAnalysisList),
            'analysisDataNames'       => $analysisDataNames
        ));
    }

    public function refererListAction(Request $request)
    {
        $query      = $request->query->all();
        $timeRange  = $this->getTimeRange($query);
        $conditions = array_merge($timeRange, array('targetType' => 'openCourse'));

        return $this->render('TopxiaAdminBundle:OpenCourseAnalysis/Referer:list.html.twig', array(
            'dateRange' => $this->getDataInfo($timeRange)
        ));
    }

    public function conversionAction(Request $request)
    {
        $timeRange  = $this->getTimeRange($request->query->all());
        $conditions = array_merge($timeRange, array('targetType' => 'openCourse'));

        $paginator = new Paginator(
            $this->get('request'),
            $this->getOpenCourseService()->searchCourseCount(array()),
            10
        );

        $courses = $this->getOpenCourseService()->searchCourses(
            array(),
            array('createdTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $conditions['targetType'] = 'openCourse';
        $refererLogs              = $this->getRefererLogService()->searchRefererLogs(
            $conditions,
            array('createdTime', 'DESC'),
            0, PHP_INT_MAX, 'targetId'
        );
        $refererLogs = ArrayToolkit::index($refererLogs, 'targetId');

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
            'dateRange' => $this->getDataInfo($timeRange)
        ));
    }

    private function prepareAnalysisDatas($refererlogDatas)
    {
        if (empty($refererlogDatas)) {
            return array();
        }
        $lenght = 6;

        $analysisDatas      = array_slice($refererlogDatas, 0, $lenght);
        $otherAnalysisDatas = count($refererlogDatas) >= $lenght ? array_slice($refererlogDatas, $lenght) : array();

        $totoalCount      = array_sum(ArrayToolkit::column($refererlogDatas, 'value'));
        $otherTotoalCount = array_sum(ArrayToolkit::column($otherAnalysisDatas, 'value'));

        array_push($analysisDatas, array('value' => $otherTotoalCount, 'name' => '其他'));
        array_walk($analysisDatas, function ($data, $key, $totoalCount) use (&$analysisDatas) {
            $analysisDatas[$key]['percent'] = round($data['value'] / $totoalCount * 100, 2).'%';
        }, $totoalCount);

        return $analysisDatas;
    }

    protected function getDataInfo($timeRange)
    {
        return array(
            'startTime'      => date("Y-m-d", $timeRange['startTime']),
            'endTime'        => date("Y-m-d", $timeRange['endTime']),
            'yesterdayStart' => date("Y-m-d", strtotime(date("Y-m-d", time())) - 24 * 3600),
            'yesterdayEnd'   => date("Y-m-d", strtotime(date("Y-m-d", time()))),
            'lastWeekStart'  => date("Y-m-d", strtotime(date("Y-m-d", strtotime("-1 week")))),
            'lastWeekEnd'    => date("Y-m-d", strtotime(date("Y-m-d", time()))),
            'lastMonthStart' => date("Y-m-d", strtotime(date("Y-m-d", time())) - 30 * 24 * 3600),
            'lastMonthEnd'   => date("Y-m-d", strtotime(date("Y-m-d", time())) - 24 * 3600)
        );
    }

    protected function getTimeRange($fields)
    {
        if (empty($fields['startTime']) && empty($fields['endTime'])) {
            return array('startTime' => strtotime(date("Y-m-d", time())) - 24 * 3600, 'endTime' => strtotime(date("Y-m-d", time())));
        }
        return array('startTime' => strtotime($fields['startTime']), 'endTime' => (strtotime($fields['endTime']) + 24 * 3600));
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

        $totalData['visitCount'] = $this->getRefererLogService()->searchRefererLogCount(array(), '');
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
