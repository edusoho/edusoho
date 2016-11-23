<?php

namespace WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class CourseSetManageController extends BaseController
{
    public function indexAction(Request $request, $id)
    {
        $courseSet     = $this->getCourseSetService()->getCourseSet($id);
        $courses       = $this->getCourseService()->findCoursesByCourseSetId($id);
        $defaultCourse = $this->getCourseService()->getDefaultCourseByCourseSetId($id);

        return $this->render('WebBundle:CourseSetManage:courses.html.twig', array(
            'courseSet'     => $courseSet,
            'courses'       => $courses,
            'defaultCourse' => $defaultCourse
        ));
    }

    public function createAction(Request $request)
    {
        return $this->render('WebBundle:CourseSetManage:courseset-create.html.twig', array(
            //params
        ));
    }

    //基础信息
    public function baseAction(Request $request, $id)
    {
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $this->getCourseSetService()->updateCourseSet($id, $data);
        }
        $courseSet     = $this->getCourseSetService()->getCourseSet($id);
        $defaultCourse = $this->getCourseService()->getDefaultCourseByCourseSetId($id);
        return $this->render('WebBundle:CourseSetManage:courseset-base.html.twig', array(
            'courseSet'     => $courseSet,
            'defaultCourse' => $defaultCourse
        ));
    }

    public function detailAction(Request $request, $id)
    {
        $courseSet     = $this->getCourseSetService()->getCourseSet($id);
        $defaultCourse = $this->getCourseService()->getDefaultCourseByCourseSetId($id);
        return $this->render('WebBundle:CourseSetManage:courseset-detail.html.twig', array(
            'courseSet'     => $courseSet,
            'defaultCourse' => $defaultCourse
        ));
    }

    public function deleteAction(Request $request, $id)
    {
        //delete..
    }

    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    protected function getCourseSetService()
    {
        return $this->getBiz()->service('Course:CourseSetService');
    }
}
