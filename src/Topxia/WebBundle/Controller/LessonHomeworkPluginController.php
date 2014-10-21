<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class LessonHomeworkPluginController extends BaseController
{

    public function listAction (Request $request)
    {
        $user = $this->getCurrentUser();
        list($course, $member) = $this->getCourseService()->tryTakeCourse($request->query->get('courseId'));

        $lesson = $this->getCourseService()->getCourseLesson($course['id'], $request->query->get('lessonId'));

        $homework = $this->getHomeworkService()->getHomeworkByCourseIdAndLessonId($course['id'], $lesson['id']);
        $exercise = $this->getExerciseService()->getExerciseByCourseIdAndLessonId($course['id'], $lesson['id']);
        $homeworkResult = $this->getHomeworkService()->getHomeworkResultByHomeworkIdAndUserId($homework['id'], $user['id']);
        return $this->render('TopxiaWebBundle:LessonHomeworkPlugin:list.html.twig', array(
            'course' => $course,
            'lesson' => $lesson,
            'homework' => $homework,
            'exercise' => $exercise,
            'homeworkResult' => $homeworkResult
        ));
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getHomeworkService()
    {
        return $this->getServiceKernel()->createService('Course.HomeworkService');
    } 

    private function getExerciseService()
    {
        return $this->getServiceKernel()->createService('Course.ExerciseService');
    }

}