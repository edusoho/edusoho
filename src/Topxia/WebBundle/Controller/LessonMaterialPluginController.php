<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class LessonMaterialPluginController extends BaseController
{

    public function initAction (Request $request)
    {
        $course = $this->getCourseService()->tryTakeCourse($request->query->get('courseId'));
        $lesson = $this->getCourseService()->getCourseLesson($course['id'], $request->query->get('lessonId'));
        $lessonMaterials = $this->getMaterialService()->findLessonMaterials($lesson['id'], 0, 100);
        return $this->render('TopxiaWebBundle:LessonMaterialPlugin:index.html.twig',array(
            'materials' => $lessonMaterials,
            'course' => $course,
            'lesson' => $lesson,
        ));
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getMaterialService()
    {
        return $this->getServiceKernel()->createService('Course.MaterialService');
    }
}