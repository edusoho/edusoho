<?php

namespace AppBundle\Controller\Course;

use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\BaseController;

class LessonManageController extends BaseController
{
    public function createAction(Request $request, $courseId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);

        $categoryId = $request->query->get('categoryId');
        $lessonCount = $this->getCourseLessonService()->countLessons($course['id']);

        if ($lessonCount >= 300) {
            return $this->createJsonResponse(array('code' => false, 'message' => 'lesson_count_no_more_than_300'));
        }

        $html = $this->renderView(
            'task-manage/modal.html.twig',
            array(
                'mode' => 'create',
                'course' => $course,
                'courseSet' => $courseSet,
                'categoryId' => $categoryId,
                'taskMode' => '',
            )
        );

        return $this->createJsonResponse(array('code' => true, 'message', 'html' => $html));
    }

    public function publishAction(Request $request, $courseId, $lessonId)
    {
        $this->getCourseService()->tryManageCourse($courseId);
        $this->getCourseLessonService()->publishLesson($lessonId);

        return $this->createJsonResponse(array('success' => true));
    }

    public function unpublishAction(Request $request, $courseId, $lessonId)
    {
        $this->getCourseService()->tryManageCourse($courseId);
        $this->getCourseLessonService()->unpublishLesson($lessonId);

        return $this->createJsonResponse(array('success' => true));
    }

    public function deleteAction(Request $request, $courseId, $lessonId)
    {
        $this->getCourseService()->tryManageCourse($courseId);
        $this->getCourseLessonService()->deleteLesson($lessonId);

        return $this->createJsonResponse(array('success' => true));
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
}
