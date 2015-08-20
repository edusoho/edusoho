<?php
namespace Custom\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Topxia\Common\Paginator;
use Topxia\Common\FileToolkit;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Util\CloudClientFactory;
use Topxia\WebBundle\Controller\CourseLessonController as BaseCourseLessonController;

class CourseLessonController extends BaseCourseLessonController
{
    public function listAction(Request $request, $courseId, $member, $previewUrl, $mode = 'full')
    {
        $user = $this->getCurrentUser();
        $learnStatuses = $this->getCourseService()->getUserLearnLessonStatuses($user['id'], $courseId);
        $items = $this->getCourseService()->getCourseItems($courseId);
        $course = $this->getCourseService()->getCourse($courseId);

        $homeworkPlugin = $this->getAppService()->findInstallApp('Homework');
        $homeworkLessonIds =array();
        $exercisesLessonIds =array();

        if($homeworkPlugin) {
            $lessonIds = ArrayToolkit::column($items, 'id');
            $homeworks = $this->getHomeworkService()->findHomeworksByCourseIdAndLessonIds($course['id'], $lessonIds);
            $exercises = $this->getExerciseService()->findExercisesByLessonIds($lessonIds);
            $homeworkLessonIds = ArrayToolkit::column($homeworks,'lessonId');
            $exercisesLessonIds = ArrayToolkit::column($exercises,'lessonId');
        }

        if ($this->setting('magic.lesson_watch_limit')) {
            $lessonLearns = $this->getCourseService()->findUserLearnedLessons($user['id'], $courseId);
        } else {
            $lessonLearns = array();
        }

        return $this->Render('CustomWebBundle:CourseLesson/Widget:list.html.twig', array(
            'items' => $items,
            'course' => $course,
            'member' => $member,
            'previewUrl' => $previewUrl,
            'learnStatuses' => $learnStatuses,
            'lessonLearns' => $lessonLearns,
            'currentTime' => time(),
            'homeworkLessonIds' => $homeworkLessonIds,
            'exercisesLessonIds' => $exercisesLessonIds,
            'mode' => $mode,
        ));
    }
}