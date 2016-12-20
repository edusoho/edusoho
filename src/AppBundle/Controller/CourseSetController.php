<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class CourseSetController extends BaseController
{
    public function showAction(Request $request, $id)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($id);

        $courseId = $request->query->get('courseId', 0);

        $course = array();
        if ($courseId > 0) {
            $course = $this->getCourseService()->getCourse($courseId);
        } else {
            $course = $this->getCourseService()->getDefaultCourseByCourseSetId($id);
        }

        return $this->render('courseset/show.html.twig', array(
            'courseSet' => $courseSet,
            'course'    => $course
        ));
    }

    public function previewAction(Request $request, $id)
    {
        $courseId = $request->query->get('courseId', 0);
        $course   = array();
        if ($courseId > 0) {
            $course = $this->getCourseService()->getCourse($courseId);
        } else {
            $course = $this->getCourseService()->getDefaultCourseByCourseSetId($id);
        }
        $courseSet = $this->getCourseSetService()->getCourseSet($id);
        return $this->render('courseset/show.html.twig', array(
            'courseSet' => $courseSet,
            'course'    => $course
        ));
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }
}
