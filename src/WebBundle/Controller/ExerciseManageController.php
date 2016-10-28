<?php
namespace WebBundle\Controller;

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;

class ExerciseManageController extends BaseController
{
    public function createAction(Request $request, $courseId, $lessonId)
    {
        list($course, $lesson) = $this->getExerciseCourseAndLesson($courseId, $lessonId);

        if ($request->getMethod() == 'POST') {
            $fields = $request->request->all();

            $fields['courseId'] = $courseId;
            $fields['lessonId'] = $lessonId;

            $exercise = $this->getTestpaperService()->buildTestpaper($fields, 'exercise');

            return $this->createJsonResponse($this->generateUrl('course_manage_lesson', array('id' => $course['id'])));
        }

        return $this->render('WebBundle:ExerciseManage:create.html.twig', array(
            'course'   => $course,
            'lesson'   => $lesson,
            'exercise' => array('id' => null)
        ));
    }

    public function updateExerciseAction(Request $request, $courseId, $lessonId, $id)
    {
        list($course, $lesson) = $this->getExerciseCourseAndLesson($courseId, $lessonId);

        $exercise = $this->getExerciseService()->getExercise($id);
        if (empty($exercise)) {
            throw $this->createNotFoundException("练习(#{$id})不存在！");
        }

        if ($request->getMethod() == 'POST') {
            $fields = $this->generateExerciseFields($request->request->all(), $course, $lesson);

            $exercise = $this->getExerciseService()->updateExercise($exercise['id'], $fields);
            return $this->createJsonResponse($this->generateUrl('course_manage_lesson', array('id' => $course['id'])));
        }

        return $this->render('HomeworkBundle:CourseExerciseManage:exercise.html.twig', array(
            'course'   => $course,
            'lesson'   => $lesson,
            'exercise' => $exercise
        ));
    }

    public function deleteExerciseAction(Request $request, $courseId, $lessonId, $id)
    {
        list($course, $lesson) = $this->getExerciseCourseAndLesson($courseId, $lessonId);

        $exercise = $this->getExerciseService()->getExercise($id);
        if (empty($exercise)) {
            throw $this->createNotFoundException("练习(#{$id})不存在！");
        }
        $this->getExerciseService()->deleteExercisesByLessonId($lessonId);
        return $this->createJsonResponse(true);
    }

    public function buildCheckAction(Request $request, $courseId, $lessonId)
    {
        list($course, $lesson) = $this->getExerciseCourseAndLesson($courseId, $lessonId);

        $fields = $request->request->all();

        $fields['courseId']                   = $course['id'];
        $fields['lessonId']                   = $lesson['id'];
        $fields['excludeUnvalidatedMaterial'] = 1;

        $result = $this->getTestpaperService()->canBuildTestpaper('exercise', $fields);

        return $this->createJsonResponse($result);
    }

    private function getExerciseCourseAndLesson($courseId, $lessonId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);

        $lesson = $this->getCourseService()->getCourseLesson($course['id'], $lessonId);
        if (empty($lesson)) {
            throw $this->createNotFoundException("课时(#{$lessonId})不存在！");
        }

        return array($course, $lesson);
    }

    protected function getTestpaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getQuestionService()
    {
        return $this->getServiceKernel()->createService('Question.QuestionService');
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}
