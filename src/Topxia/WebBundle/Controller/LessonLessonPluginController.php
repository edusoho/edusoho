<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class LessonLessonPluginController extends BaseController
{

    public function listAction (Request $request)
    {
        $user = $this->getCurrentUser();
        $course = $this->getCourseService()->tryTakeCourse($request->query->get('courseId'));

        $items = $this->getCourseService()->getCourseItems($course['id']);
        $learnStatuses = $this->getCourseService()->getUserLearnLessonStatuses($user['id'], $course['id']);

        return $this->render('TopxiaWebBundle:LessonLessonPlugin:list.html.twig', array(
            'course' => $course,
            'items' => $items,
            'learnStatuses' => $learnStatuses,
        ));
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

}