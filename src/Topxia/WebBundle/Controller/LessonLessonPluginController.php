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

        $materialLib = $this->getAppService()->findInstallApp('materialLib');
        $homeworkLessonIds =array();
        $exercisesLessonIds =array();
        $sameLessonIds =array();
        $diffLessonIdsBetweenHomeworkAndSame = array();
        $diffLessonIdsBetweenExerciseAndSame = array();

        if($materialLib){
            $lessons = $this->getCourseService()->getCourseLessons($course['id']);
            $lessonIds = ArrayToolkit::column($lessons, 'id');
            $homeworks = $this->getHomeworkService()->findHomeworksByCourseIdAndLessonIds($course['id'], $lessonIds);
            $exercises = $this->getExerciseService()->findExercisesByLessonIds($lessonIds);
            $homeworkLessonIds = ArrayToolkit::column($homeworks,'lessonId');
            $exercisesLessonIds = ArrayToolkit::column($exercises,'lessonId');
            $sameLessonIds=array_intersect($homeworkLessonIds,$exercisesLessonIds);
            $homeworkLessonIdNum = count($homeworkLessonIds);
            $exercisesLessonIdNum = count($exercisesLessonIds);
            $sameLessonIdNum = count($sameLessonIds);

            if($exercisesLessonIdNum > $sameLessonIdNum){
                foreach ($exercisesLessonIds as $key => $value) {
                    if(!in_array($value,$sameLessonIds)){
                        $diffLessonIdsBetweenHomeworkAndSame[]=$value;
                    }
                }
            }

            if($homeworkLessonIdNum > $sameLessonIdNum){
                foreach ($homeworkLessonIds as $key => $value) {
                    if(!in_array($value,$sameLessonIds)){
                        $diffLessonIdsBetweenExerciseAndSame[]=$value;
                    }
                }
            }
            $lessonIds=array_merge($sameLessonIds,$diffLessonIdsBetweenHomeworkAndSame,$diffLessonIdsBetweenExerciseAndSame);
        }

        return $this->render('TopxiaWebBundle:LessonLessonPlugin:list.html.twig', array(
            'course' => $course,
            'items' => $items,
            'learnStatuses' => $learnStatuses,
            'currentTime' => time(),
            'weeks' => array("日","一","二","三","四","五","六"),
            'lessonIds'=>empty($lessonIds)?array():$lessonIds,
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