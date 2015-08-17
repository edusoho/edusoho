<?php
namespace Custom\WebBundle\Controller;

use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Homework\HomeworkBundle\Controller\CourseHomeworkController as BaseCourseHomeworkController;
use Topxia\Common\Paginator;

class CourseHomeworkController extends BaseCourseHomeworkController
{
    public function submitAction(Request $request, $courseId, $homeworkId)
    {
        if ($request->getMethod() == 'POST') {
            $data = $request->request->all();
            $data = !empty($data['data']) ? $data['data'] : array();
            $res = $this->getHomeworkService()->submitHomework($homeworkId, $data);
            $course = $this->getCourseService()->getCourse($courseId);
            $lesson = $this->getCourseService()->getCourseLesson($courseId, $res['lessonId']);
            $this->getHomeworkService()->finishHomework($course, $lesson, $courseId, $homeworkId);
            if (!empty($res) && !empty($res['lessonId'])) {
                return $this->createJsonResponse(array('courseId' => $courseId, 'lessonId' => $res['lessonId']));
            } else {
                throw $this->createServiceException('用户不存在或者尚未登录，请先登录');
            }
        }
    }

    public function checkListAction(Request $request, $courseId, $status)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);
        $homeworkResultsCounts = $this->getHomeworkService()->findResultsCountsByCourseIdAndStatus($courseId, $status);
        $paginator = new Paginator(
            $this->get('request'),
            $homeworkResultsCounts
            , 5
        );

        if ($status == 'reviewing' or $status == 'pairReviewing') {
            $orderBy = array('updatedTime', 'DESC');
        }

        if ($status == 'finished') {
            $orderBy = array('checkedTime', 'DESC');
        }

        $homeworkResults = $this->getHomeworkService()->findResultsByCourseIdAndStatus(
            $courseId, $status, $orderBy,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $usersIds = ArrayToolkit::column($homeworkResults, 'userId');
        $users = $this->getUserService()->findUsersByIds($usersIds);

        $lessonIds = ArrayToolkit::column($homeworkResults, 'lessonId');
        $lessons = $this->getCourseService()->findLessonsByIds($lessonIds);

        if ($status == 'reviewing') {
            $reviewingCount = $homeworkResultsCounts;
            $finishedCount = $this->getHomeworkService()->findResultsCountsByCourseIdAndStatus($courseId, 'finished');
        }

        if ($status == 'finished') {
            $reviewingCount = $this->getHomeworkService()->findResultsCountsByCourseIdAndStatus($courseId, 'reviewing');
            $finishedCount = $homeworkResultsCounts;
        }

        if ($status == 'pairReviewing') {
            $reviewingCount = $this->getHomeworkService()->findResultsCountsByCourseIdAndStatus($courseId, 'reviewing');
            $finishedCount = $homeworkResultsCounts;
        }

        return $this->render('CustomWebBundle:CourseHomework:check-list.html.twig', array(
            'status' => $status,
            'homeworkResults' => $homeworkResults,
            'course' => $course,
            'users' => $users,
            'reviewingCount' => $reviewingCount,
            'finishedCount' => $finishedCount,
            'pairReviewingCount' => 0,
            'lessons' => $lessons,
            'paginator' => $paginator
        ));
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    private function getHomeworkService()
    {
        return $this->getServiceKernel()->createService('Custom:Homework.HomeworkService');
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}