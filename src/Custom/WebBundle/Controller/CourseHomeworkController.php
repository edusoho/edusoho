<?php
namespace Custom\WebBundle\Controller;

use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Homework\HomeworkBundle\Controller\CourseHomeworkController as BaseCourseHomeworkController;
use Topxia\Common\Paginator;

class CourseHomeworkController extends BaseCourseHomeworkController
{
    public function doAction(Request $request, $courseId, $homeworkId, $resultId)
    {
        list($course, $member) = $this->getCourseService()->tryTakeCourse($courseId);
        $homework = $this->getHomeworkService()->getHomework($homeworkId);
        if (empty($homework)) {
            throw $this->createNotFoundException();
        }

        if ($homework['courseId'] != $course['id']) {
            throw $this->createNotFoundException();
        }

        $lesson = $this->getCourseService()->getCourseLesson($homework['courseId'], $homework['lessonId']);

        if (empty($lesson)) {
            return $this->createMessageResponse('info', '作业所属课时不存在！');
        }

        $itemSet = $this->getHomeworkService()->getItemSetByHomeworkId($homework['id']);
        $homeworkResult = $this->getHomeworkService()->getResultByLessonIdAndUserId($homework['lessonId'], $this->getCurrentUser()->id);

        return $this->render('CustomWebBundle:CourseHomework:do.html.twig', array(
            'homework' => $homework,
            'itemSet' => $itemSet,
            'course' => $course,
            'lesson' => $lesson,
            'now' => time(),
            'homeworkResult' => $homeworkResult,
            'view' => 'doing'
        ));
    }

    public function saveHomeworkResultAction(Request $request, $homeworkResultId)
    {
        if ($request->getMethod() == 'POST') {
            $data = $request->request->all();
            $items = !empty($data['data']) ? $data['data'] : array();
            $res = $this->getHomeworkService()->saveHomeworkResultItems($homeworkResultId,$items);
            if ($res && !empty($res['lessonId'])) {
               return $this->createJsonResponse(array('courseId' => $res['courseId'],'lessonId' => $res['lessonId']));
            }
        }
    }

    public function continueAction(Request $request, $courseId, $homeworkId, $resultId)
    {
        list($course, $member) = $this->getCourseService()->tryTakeCourse($courseId);
        $homework = $this->getHomeworkService()->getHomework($homeworkId);
        if (empty($homework)) {
            throw $this->createNotFoundException();
        }

        if ($homework['courseId'] != $course['id']) {
            throw $this->createNotFoundException();
        }

        $lesson = $this->getCourseService()->getCourseLesson($homework['courseId'], $homework['lessonId']);

        if (empty($lesson)) {
            return $this->createMessageResponse('info', '作业所属课时不存在！');
        }

        $itemSetResult = $this->getHomeworkService()->getItemSetResultByHomeworkIdAndUserId($homework['id'], $this->getCurrentuser()->id);
        $homeworkResult = $this->getHomeworkService()->getResultByLessonIdAndUserId($homework['lessonId'], $this->getCurrentUser()->id);

//print_r($itemSetResult);
        return $this->render('CustomWebBundle:CourseHomework:do.html.twig', array(
            'homework' => $homework,
            'itemSetResult' => $itemSetResult,
            'course' => $course,
            'lesson' => $lesson,
            'now' => time(),
            'homeworkResult' => $homeworkResult,
            'view' => 'doing'
        ));
    }

    public function submitAction(Request $request, $courseId, $homeworkId)
    {
        if ($request->getMethod() == 'POST') {
            $data = $request->request->all();
            $data = !empty($data['data']) ? $data['data'] : array();

            $homework=$this->getHomeworkService()->loadHomework($homeworkId);
            if ($homework['pairReview'] and intval($homework['completeTime']) < time()) {
                return $this->createMessageResponse('error',"已经超过作业提交截止时间，提交作业失败！");
            }
            $res = $this->getHomeworkService()->submitHomework($data,$this->getCurrentUser()->id);

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
            $pairReviewingCount = $this->getHomeworkService()->findResultsCountsByCourseIdAndStatus($courseId, 'pairReviewing');
        }

        if ($status == 'finished') {
            $reviewingCount = $this->getHomeworkService()->findResultsCountsByCourseIdAndStatus($courseId, 'reviewing');
            $finishedCount = $homeworkResultsCounts;
            $pairReviewingCount = $this->getHomeworkService()->findResultsCountsByCourseIdAndStatus($courseId, 'pairReviewing');
        }

        if ($status == 'pairReviewing') {
            $reviewingCount = $this->getHomeworkService()->findResultsCountsByCourseIdAndStatus($courseId, 'reviewing');
            $finishedCount = $this->getHomeworkService()->findResultsCountsByCourseIdAndStatus($courseId, 'finished');
            $pairReviewingCount = $homeworkResultsCounts;
        }

        return $this->render('CustomWebBundle:CourseHomework:check-list.html.twig', array(
            'status' => $status,
            'homeworkResults' => $homeworkResults,
            'course' => $course,
            'users' => $users,
            'reviewingCount' => $reviewingCount,
            'finishedCount' => $finishedCount,
            'pairReviewingCount' => $pairReviewingCount,
            'lessons' => $lessons,
            'paginator' => $paginator
        ));
    }

    public function resultAction(Request $request, $courseId, $homeworkId, $resultId, $userId)
    {
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException('您尚未登录用户，请登录后再查看！');
        }

        $course = $this->getCourseService()->getCourse($courseId);
        if (empty($course)) {
            throw $this->createNotFoundException('此课程不存在或者已删除！');
        }

        $canLookHomeworkResult = $this->getHomeworkService()->canLookHomeworkResult($resultId);
        if (!$canLookHomeworkResult) {
            throw $this->createAccessDeniedException('无权查看作业！');
        }

        $homework = $this->getHomeworkService()->getHomework($homeworkId);

        if ($homework['courseId'] != $course['id']) {
            throw $this->createNotFoundException();
        }

        $lesson = $this->getCourseService()->getCourseLesson($homework['courseId'], $homework['lessonId']);

        if (empty($lesson)) {
            return $this->createMessageResponse('info', '作业所属课时不存在！');
        }

        $itemSetResult = $this->getHomeworkService()->getItemSetResultByHomeworkIdAndUserId($homework['id'], $userId);
        $homeworkResult = $this->getHomeworkService()->getResultByLessonIdAndUserId($homework['lessonId'], $userId);

        $reviewItems = ('finished'==$homeworkResult['status']) ? $this->getHomeworkService()->getIndexedReviewItems($homeworkResult['id']) : null;

        return $this->render('CustomWebBundle:CourseHomework:result.html.twig', array(
            'homework' => $homework,
            'itemSetResult' => $itemSetResult,
            'course' => $course,
            'lesson' => $lesson,
            'teacherSay' => $homeworkResult['teacherSay'],
            'userId' => $homeworkResult['userId'],
            'homeworkResult' => $homeworkResult,
            'reviewItems' => $reviewItems,
            'view' => 'show'
        ));
    }

    public function previewAction(Request $request, $courseId, $homeworkId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);
        $homework = $this->getHomeworkService()->getHomework($homeworkId);

        if (empty($homework)) {
            throw $this->createNotFoundException();
        }

        if ($homework['courseId'] != $course['id']) {
            throw $this->createNotFoundException();
        }

        $lesson = $this->getCourseService()->getCourseLesson($homework['courseId'], $homework['lessonId']);
        if (empty($lesson)) {
            return $this->createMessageResponse('info','作业所属课时不存在！');
        }

        $itemSet = $this->getHomeworkService()->getItemSetByHomeworkId($homework['id']);
        $homeworkResult = $this->getHomeworkService()->getResultByLessonIdAndUserId($homework['lessonId'], $this->getCurrentUser()->id);

        return $this->render('CustomWebBundle:CourseHomework:preview.html.twig', array(
            'homework' => $homework,
            'itemSet' => $itemSet,
            'course' => $course,
            'lesson' => $lesson,
            'homeworkResult' => $homeworkResult,
            'view' => 'preview'
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