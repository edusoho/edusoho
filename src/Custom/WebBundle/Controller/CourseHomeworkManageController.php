<?php
namespace Custom\WebBundle\Controller;

use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use Homework\HomeworkBundle\Controller\CourseHomeworkManageController as BaseManageController;
use Topxia\Common\Paginator;

class CourseHomeworkManageController extends BaseManageController
{
    public function createAction(Request $request, $courseId, $lessonId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);
        $lesson = $this->getCourseService()->getCourseLesson($course['id'], $lessonId);

        if (empty($course)) {
            throw $this->createNotFoundException("课程(#{$courseId})不存在！");
        }

        if (empty($lesson)) {
            throw $this->createNotFoundException("课时(#{$lessonId})不存在！");
        }

        if ($request->getMethod() == 'POST') {

            $fields = $request->request->all();
            $homework = $this->getHomeworkService()->createHomework($courseId, $lessonId, $fields);

            if ($homework) {
                return $this->createJsonResponse(array("status" => "success", 'courseId' => $courseId));
            } else {
                return $this->createJsonResponse(array("status" => "failed"));
            }
        }

        return $this->render('CustomWebBundle:CourseHomeworkManage:homework-modal.html.twig', array(
            'course' => $course,
            'lesson' => $lesson,
        ));
    }

    public function listAction(Request $request)
    {
        $status = $request->query->get('status', 'finished');
        $currentUser = $this->getCurrentUser();

        $conditions = array(
            'status' => $status,
            'userId' => $currentUser['id']
        );
        $paginator = new Paginator(
            $this->get('request'),
            $this->getHomeworkService()->searchResultsCount($conditions),
            25
        );
        $homeworkResults = $this->getHomeworkService()->searchResults(
            $conditions,
            array('updatedTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $homeworkCourseIds = ArrayToolkit::column($homeworkResults, 'courseId');
        $homeworkLessonIds = ArrayToolkit::column($homeworkResults, 'lessonId');
        $courses = $this->getCourseService()->findCoursesByIds($homeworkCourseIds);
        $lessons = $this->getCourseService()->findLessonsByIds($homeworkLessonIds);

        return $this->render('CustomWebBundle:CourseHomeworkManage:list.html.twig', array(
            'status' => $status,
            'homeworkResults' => $homeworkResults,
            'courses' => $courses,
            'lessons' => $lessons,
            'user' => $currentUser,
            'paginator' => $paginator
        ));
    }

    private function getHomeworkService()
    {
        return $this->getServiceKernel()->createService('Custom:Homework.HomeworkService');
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getQuestionService()
    {
        return $this->getServiceKernel()->createService('Question.QuestionService');
    }

    private function getMessageService()
    {
        return $this->getServiceKernel()->createService('User.MessageService');
    }

}