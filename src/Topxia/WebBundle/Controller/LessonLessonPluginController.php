<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;

class LessonLessonPluginController extends BaseController
{

    public function listAction (Request $request)
    {
        $user = $this->getCurrentUser();
        list($course, $member) = $this->getCourseService()->tryTakeCourse($request->query->get('courseId'));

        $items = $this->getCourseService()->getCourseItems($course['id']);
        $learnStatuses = $this->getCourseService()->getUserLearnLessonStatuses($user['id'], $course['id']);

        $homeworkPlugin = $this->getAppService()->findInstallApp('Homework');
        $homeworkLessonIds =array();
        $exercisesLessonIds =array();

        if($homeworkPlugin) {
            $lessons = $this->getCourseService()->getCourseLessons($course['id']);
            $lessonIds = ArrayToolkit::column($lessons, 'id');
            $homeworks = $this->getHomeworkService()->findHomeworksByCourseIdAndLessonIds($course['id'], $lessonIds);
            $exercises = $this->getExerciseService()->findExercisesByLessonIds($lessonIds);
            $homeworkLessonIds = ArrayToolkit::column($homeworks,'lessonId');
            $exercisesLessonIds = ArrayToolkit::column($exercises,'lessonId');
        }

        return $this->render('TopxiaWebBundle:LessonLessonPlugin:list.html.twig', array(
            'course' => $course,
            'items' => $items,
            'learnStatuses' => $learnStatuses,
            'currentTime' => time(),
            'weeks' => array("日","一","二","三","四","五","六"),
            'homeworkLessonIds' => $homeworkLessonIds,
            'exercisesLessonIds' => $exercisesLessonIds,
        ));
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getAppService()
    {
        return $this->getServiceKernel()->createService('CloudPlatform.AppService');
    }

    private function getHomeworkService()
    {
            return $this->getServiceKernel()->createService('Homework:Homework.HomeworkService');
    } 

    private function getExerciseService()
    {
            return $this->getServiceKernel()->createService('Homework:Homework.ExerciseService');
    }

}