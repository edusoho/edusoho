<?php
namespace Topxia\AdminBundle\Controller;

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
}
