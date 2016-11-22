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

        $defualtCourse['locked'] = $defaultCourse['status'] == 'closed';
        $courseSet['locked']     = $courseSet['status'] == 'closed';
        return $this->render('WebBundle:CourseSetManage:courses.html.twig', array(
            'courseSet'     => $courseSet,
            'courses'       => $courses,
            'defaultCourse' => $defualtCourse
        ));
    }

    public function createAction(Request $request)
    {
        return $this->render('WebBundle:CourseSetManage:create.html.twig', array(
            //params
        ));
    }

    public function previewAction(Request $request, $id)
    {
        // 预览courseSet
    }

    public function deleteAction(Request $request, $courseSetId)
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

    protected function getPermissionExtension()
    {
        return $this->container->get('permission.twig.permission_extension');
    }
}
