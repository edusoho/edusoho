<?php

namespace WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class CourseSetController extends BaseController
{
    public function showAction(Request $request, $id)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($id);
        $courseId  = $request->query->get('courseId', 0);
        $course    = array();
        if ($courseId > 0) {
            $course = $this->getCourseService()->getCourse($courseId);
        } else {
            $course = $this->getCourseService()->getDefaultCourseByCourseSetId($id);
        }

        return $this->render('WebBundle:CourseSet:show.html.twig', array(
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
        return $this->render('WebBundle:CourseSet:preview.html.twig', array(
            'courseSet' => $courseSet,
            'course'    => $course
        ));
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
