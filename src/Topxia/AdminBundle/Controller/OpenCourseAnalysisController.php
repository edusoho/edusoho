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
        return $this->render('TopxiaAdminBundle:OpenCourseAnalysis/Referer:index.html.twig');
    }

    public function conversionAction(Request $request)
    {
        $conditions = $request->query->all();

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
        $refererLogs              = $this->getRefererLogService()->searchRefererLogsGroupByTargetId($conditions, array('createdTime', 'DESC'), 0, PHP_INT_MAX);
        $refererLogs              = ArrayToolkit::index($refererLogs, 'targetId');

        return $this->render('TopxiaAdminBundle:OpenCourseAnalysis/conversion:index.html.twig', array(
            'courses'     => $courses,
            'paginator'   => $paginator,
            'refererLogs' => $refererLogs
        ));
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
}
