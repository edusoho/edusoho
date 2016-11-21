<?php

namespace WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class CourseManageController extends BaseController
{
    public function createAction(Request $request)
    {
        $data   = $request->request->all();
        $course = $this->getCourseService()->createCourse($data);

        return $this->createJsonResponse($course);
    }

    public function infoAction(Request $request, $courseSetId, $courseId)
    {
        return $this->render('WebBundle:CourseSetManage:course_info.html.twig', array());
    }

    public function previewAction(Request $request, $courseSetId, $courseId)
    {
        return $this->render('WebBundle:CourseSet:preview.html.twig', array());
    }

    public function listAction(Request $request, $courseSetId)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);
        $courses   = $this->getCourseService()->findCoursesByCourseSetId($courseSetId);
        return $this->render('WebBundle:CourseSetManage:courses.html.twig', array(
            'courseSet' => $courseSet,
            'courses'   => $courses
        ));
    }

    public function marketingAction(Request $request, $courseSetId, $courseId)
    {
        return $this->render('WebBundle:CourseSetManage:course_marketing.html.twig', array());
    }

    public function closeAction(Request $request, $courseSetId, $courseId)
    {
        //TODO
        return $this->createJsonResponse(array('success' => true));
    }

    public function preparePublishment(Request $request, $courseSetId, $courseId)
    {
        //TODO
        return $this->createJsonpResponse(array('success' => true));
    }

    public function auditPublishment(Request $request, $courseSetId, $courseId)
    {
        //管理员进行审核
        return $this->createJsonResponse(array('success' => true));
    }

    protected function getCourseSetService()
    {
        return $this->getBiz()->service('Course:CourseSetService');
    }

    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }
}
