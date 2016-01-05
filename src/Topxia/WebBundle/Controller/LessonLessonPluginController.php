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

        $testpaperIds = array();
        array_walk($items, function($item, $key)use(&$testpaperIds){
            if($item['type'] == 'testpaper'){
                array_push($testpaperIds, $item['mediaId']);
            }
        });

        $testpapers = $this->getTestpaperService()->findTestpapersByIds($testpaperIds);

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
            'member' => $member,
            'testpapers' => $testpapers
        ));
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getAppService()
    {
        return $this->getServiceKernel()->createService('CloudPlatform.AppService');
    }

    protected function getHomeworkService()
    {
        return $this->getServiceKernel()->createService('Homework:Homework.HomeworkService');
    } 

    protected function getExerciseService()
    {
        return $this->getServiceKernel()->createService('Homework:Homework.ExerciseService');
    }

    protected function getTestpaperService()
    {
        return $this->getServiceKernel()->createService('Testpaper.TestpaperService');
    }
}