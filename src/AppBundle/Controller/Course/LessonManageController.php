<?php

namespace AppBundle\Controller\Course;

use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\BaseController;

class LessonManageController extends BaseController
{
    public function createAction(Request $request, $courseId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);

        $this->getCourseLessonService()->isLessonCountEnough($course['id']);

        if ($request->getMethod() == 'POST') {
            $formData = $request->request->all();

            $formData['_base_url'] = $request->getSchemeAndHttpHost();
            $formData['fromUserId'] = $this->getUser()->getId();
            $formData['fromCourseSetId'] = $course['courseSetId'];
            list($lesson, $task) = $this->getCourseLessonService()->createLesson($formData);

            return $this->getTaskJsonView($course, $task);
        }

        return $this->forward('AppBundle:TaskManage:create', array('courseId' => $course['id']));
    }

    public function updateAction(Request $request, $courseId, $lessonId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);
        $lesson = $this->getCourseService()->getChapter($courseId, $lessonId);

        if ($request->getMethod() == 'POST') {
            $fields = $request->request->all();
            $lesson = $this->getCourseLessonService()->updateLesson($lesson['id'], $fields);

            return $this->render('lesson-manage/chapter/item.html.twig', array(
                'course' => $course,
                'chapter' => $lesson,
            ));
        }

        return $this->render('lesson-manage/chapter/modal.html.twig', array(
            'course' => $course,
            'type' => 'lesson',
            'chapter' => $lesson,
        ));
    }

    public function publishAction(Request $request, $courseId, $lessonId)
    {
        $this->getCourseLessonService()->publishLesson($courseId, $lessonId);

        return $this->createJsonResponse(array('success' => true));
    }

    public function unpublishAction(Request $request, $courseId, $lessonId)
    {
        $this->getCourseLessonService()->unpublishLesson($courseId, $lessonId);

        return $this->createJsonResponse(array('success' => true));
    }

    public function deleteAction(Request $request, $courseId, $lessonId)
    {
        $this->getCourseLessonService()->deleteLesson($courseId, $lessonId);

        return $this->createJsonResponse(array('success' => true));
    }

    //创建任务或修改任务返回的html
    protected function getTaskJsonView($course, $task)
    {
        $taskJsonData = $this->createCourseStrategy($course)->getTasksJsonData($task);
        if (empty($taskJsonData)) {
            return $this->createJsonResponse(false);
        }

        return $this->createJsonResponse($this->renderView(
            $taskJsonData['template'],
            $taskJsonData['data']
        ));
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    protected function getCourseLessonService()
    {
        return $this->createService('Course:LessonService');
    }

    protected function createCourseStrategy($course)
    {
        return $this->getBiz()->offsetGet('course.strategy_context')->createStrategy($course['courseType']);
    }
}
