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

    public function editAction(Request $request, $courseId, $lessonId, $homeworkId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);
        $lesson = $this->getCourseService()->getCourseLesson($course['id'], $lessonId);

        if (empty($course)) {
            throw $this->createNotFoundException("课程(#{$courseId})不存在！");
        }

        if (empty($lesson)) {
            throw $this->createNotFoundException("课时(#{$lessonId})不存在！");
        }

        $homework = $this->getHomeworkService()->getHomework($homeworkId);

        if (empty($homework)) {
            throw $this->createNotFoundException("作业(#{$homeworkId})不存在！");
        }

        $homeworkItems = $this->getHomeworkService()->findItemsByHomeworkId($homeworkId);
        $homeworkItemsArray = array();

        foreach ($homeworkItems as $key => $homeworkItem) {
            if ($homeworkItem['parentId'] == "0") {
                $homeworkItemsArray[] = $homeworkItem;
            }
        }

        $homeworkItems = $homeworkItemsArray;
        $questions = $this->getQuestionService()->findQuestionsByIds(ArrayToolkit::column($homeworkItems, 'questionId'));

        if ($request->getMethod() == 'POST') {

            $fields = $request->request->all();
            $homework = $this->getHomeworkService()->updateHomework($homeworkId, $fields);

            if ($homework) {
                return $this->createJsonResponse(array("status" => "success", 'courseId' => $courseId));
            } else {
                return $this->createJsonResponse(array("status" => "failed"));
            }
        }
        if ($homework['pairReview'])
            $homework['scoreRule'] = $homework['completePercent'] . ":"
                . $homework['partPercent'] . ":" . $homework['zeroPercent'] . ":" . $homework['minReviews'];

        return $this->render('CustomWebBundle:CourseHomeworkManage:homework-edit.html.twig', array(
            'course' => $course,
            'lesson' => $lesson,
            'homework' => $homework,
            'homeworkItems' => $homeworkItems,
            'questions' => $questions,
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

    public function teachingListAction(Request $request)
    {
        $status = $request->query->get('status', 'reviewing');
        $currentUser = $this->getCurrentUser();
        if (empty($currentUser)) {
            throw $this->createServiceException('用户不存在或者尚未登录，请先登录');
        }

        $courses = $this->getCourseService()->findUserTeachCourses(array("userId" => $currentUser['id']), 0, PHP_INT_MAX, false);
        $courseIds = ArrayToolkit::column($courses, 'id');
        $homeworksResultsCounts = $this->getHomeworkService()->findResultsCountsByCourseIdsAndStatus($courseIds, $status);
        $paginator = new Paginator(
            $this->get('request'),
            $homeworksResultsCounts
            , 5
        );

        if ($status == 'reviewing' or $status == 'pairReviewing') {
            $orderBy = array('usedTime', 'DESC');
        }

        if ($status == 'finished') {
            $orderBy = array('checkedTime', 'DESC');
        }

        $homeworksResults = $this->getHomeworkService()->findResultsByCourseIdsAndStatus(
            $courseIds, $status, $orderBy,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        if ($status == 'reviewing') {
            $reviewingCount = $homeworksResultsCounts;
            $finishedCount = $this->getHomeworkService()->findResultsCountsByCourseIdsAndStatus($courseIds, 'finished');
            $pairReviewingCount = $this->getHomeworkService()->findResultsCountsByCourseIdsAndStatus($courseIds, 'pairReviewing');
        }

        if ($status == 'finished') {
            $reviewingCount = $this->getHomeworkService()->findResultsCountsByCourseIdsAndStatus($courseIds, 'reviewing');
            $finishedCount = $homeworksResultsCounts;
            $pairReviewingCount = $this->getHomeworkService()->findResultsCountsByCourseIdsAndStatus($courseIds, 'pairReviewing');
        }

        if ($status == 'pairReviewing') {
            $reviewingCount = $this->getHomeworkService()->findResultsCountsByCourseIdsAndStatus($courseIds, 'reviewing');
            $pairReviewingCount = $homeworksResultsCounts;
            $finishedCount = $this->getHomeworkService()->findResultsCountsByCourseIdsAndStatus($courseIds, 'finished');
        }

        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($homeworksResults, 'courseId'));
        $lessons = $this->getCourseService()->findLessonsByIds(ArrayToolkit::column($homeworksResults, 'lessonId'));

        $usersIds = ArrayToolkit::column($homeworksResults, 'userId');
        $users = $this->getUserService()->findUsersByIds($usersIds);

        return $this->render('HomeworkBundle:CourseHomeworkManage:teaching-list.html.twig', array(
            'status' => $status,
            'users' => $users,
            'homeworksResults' => $homeworksResults,
            'paginator' => $paginator,
            'courses' => $courses,
            'lessons' => $lessons,
            'reviewingCount' => $reviewingCount,
            'finishedCount' => $finishedCount,
            'pairReviewingCount' => $pairReviewingCount
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