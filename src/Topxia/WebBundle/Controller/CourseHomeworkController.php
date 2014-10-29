<?php
namespace Topxia\WebBundle\Controller;

use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\Paginator;

class CourseHomeworkController extends BaseController
{
    public function startDoAction(Request $request, $courseId, $homeworkId)
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
            return $this->createMessageResponse('info','作业所属课时不存在！');
        }

        $result = $this->getHomeworkService()->startHomework($homeworkId);
        return $this->redirect($this->generateUrl('course_homework_do', 
            array(
                'courseId' => $result['courseId'],
                'homeworkId' => $result['homeworkId'],
                'resultId' => $result['id'],
            ))
        );
    }

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
            return $this->createMessageResponse('info','作业所属课时不存在！');
        }

        $itemSet = $this->getHomeworkService()->getItemSetByHomeworkId($homework['id']);

        return $this->render('TopxiaWebBundle:CourseHomework:do.html.twig', array(
            'homework' => $homework,
            'itemSet' => $itemSet,
            'course' => $course,
            'lesson' => $lesson,
            'questionStatus' => 'doing'
        ));
    }

    public function submitAction(Request $request,$courseId,$homeworkId)
    {
        if ($request->getMethod() == 'POST') {
            $data = $request->request->all();
            $data = !empty($data['data']) ? $data['data'] : array();
            $res = $this->getHomeworkService()->submitHomework($homeworkId,$data);
            if (!empty($res) && !empty($res['lessonId'])) {
               return $this->createJsonResponse(array('courseId' => $courseId,'lessonId' => $res['lessonId']));
            }
        }
    }
    
    public function saveAction(Request $request ,$courseId,$homeworkId)
    {
        if ($request->getMethod() == 'POST') {
            $data = $request->request->all();
            $homework = !empty($data['data']) ? $data['data'] : array();
            $res = $this->getHomeworkService()->saveHomework($homeworkId,$homework);
            if ($res && !empty($res['lessonId'])) {
               return $this->createJsonResponse(array('courseId' => $courseId,'lessonId' => $res['lessonId']));
            }
        }
    }

    public function continueAction(Request $request ,$courseId,$homeworkId, $resultId)
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
            return $this->createMessageResponse('info','作业所属课时不存在！');
        }

        $itemSetResult = $this->getHomeworkService()->getItemSetResultByHomeworkIdAndUserId($homework['id'],$this->getCurrentuser()->id);
        return $this->render('TopxiaWebBundle:CourseHomework:continue.html.twig', array(
            'homework' => $homework,
            'itemSetResult' => $itemSetResult,
            'course' => $course,
            'lesson' => $lesson,
            'questionStatus' => 'continue'
        ));
    }

    public function resultAction(Request $request, $courseId, $homeworkId, $resultId ,$userId)
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
            return $this->createMessageResponse('info','作业所属课时不存在！');
        }

        $itemSetResult = $this->getHomeworkService()->getItemSetResultByHomeworkIdAndUserId($homework['id'],$userId);
        $homeworkResult = $this->getHomeworkService()->getHomeworkResultByCourseIdAndLessonIdAndUserId($courseId, $homework['lessonId'], $userId);

        return $this->render('TopxiaWebBundle:CourseHomework:result.html.twig', array(
            'homework' => $homework,
            'itemSetResult' => $itemSetResult,
            'course' => $course,
            'lesson' => $lesson,
            'teacherSay' => $homeworkResult['teacherSay'],
            'userId' => $homeworkResult['userId'],
            'questionStatus' => 'finished'
        ));
    }

    public function checkShowAction(Request $request, $courseId, $homeworkId,$userId)
    {
        return $this->render('TopxiaWebBundle:CourseHomework:check-modal.html.twig',array(
            'courseId' => $courseId,
            'homeworkId' => $homeworkId,
            'userId' => $userId
        ));
    }

    public function checkListAction(Request $request ,$courseId, $status)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);
        $homeworkResultsCounts = $this->getHomeworkService()->findHomeworkResultsCountsByCourseIdAndStatus($courseId, $status);
        $paginator = new Paginator(
            $this->get('request'),
            $homeworkResultsCounts
            , 5
        );

        $homeworkResults = $this->getHomeworkService()->findHomeworkResultsByCourseIdAndStatus(
           $courseId, $status,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $usersIds = ArrayToolkit::column($homeworkResults,'userId');
        $users = $this->getUserService()->findUsersByIds($usersIds);

        $lessonIds = ArrayToolkit::column($homeworkResults,'lessonId');
        $lessons = $this->getCourseService()->findLessonsByIds($lessonIds);

        if ($status == 'reviewing') {
            $reviewingCount = $homeworkResultsCounts;
            $finishedCount = $this->getHomeworkService()->findHomeworkResultsCountsByCourseIdAndStatus($courseId,'finished');
        }

        if ($status == 'finished') {
            $reviewingCount = $this->getHomeworkService()->findHomeworkResultsCountsByCourseIdAndStatus($courseId,'reviewing');
            $finishedCount = $homeworkResultsCounts;
        }

        return $this->render('TopxiaWebBundle:CourseHomework:check-list.html.twig', array(
            'status' => $status,
            'homeworkResults' => $homeworkResults,
            'course' => $course,
            'users' => $users,
            'reviewingCount' => $reviewingCount,
            'finishedCount' => $finishedCount,
            'lessons' => $lessons,
            'paginator' => $paginator
        ));
    }

    public function checkAction(Request $request, $courseId, $homeworkId,$userId)
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
            return $this->createMessageResponse('info','作业所属课时不存在！');
        }

        if ($request->getMethod() == 'POST') {

            $checkHomeworkData = $request->request->all();
            $checkHomeworkData = empty($checkHomeworkData['data']) ? "" : $checkHomeworkData['data'];
            $this->getHomeworkService()->checkHomework($homeworkId,$userId,$checkHomeworkData);

            return $this->createJsonResponse(
                array(
                    'courseId' => $courseId,
                    'lessonId' => $homework['lessonId']
                )
            );
        }

        $itemSetResult = $this->getHomeworkService()->getItemSetResultByHomeworkIdAndUserId($homework['id'],$userId);
        return $this->render('TopxiaWebBundle:CourseHomework:check.html.twig', array(
            'homework' => $homework,
            'itemSetResult' => $itemSetResult,
            'course' => $course,
            'lesson' => $lesson,
            'userId' => $userId,
            'questionStatus' => 'reviewing'
        ));
    }

    public function previewAction(Request $request, $courseId, $homeworkId)
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
            return $this->createMessageResponse('info','作业所属课时不存在！');
        }

        $itemSet = $this->getHomeworkService()->getItemSetByHomeworkId($homework['id']);

        return $this->render('TopxiaWebBundle:CourseHomework:preview.html.twig', array(
            'homework' => $homework,
            'itemSet' => $itemSet,
            'course' => $course,
            'lesson' => $lesson,
            'questionStatus' => 'previewing'
        ));   
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    private function getQuestionService()
    {
        return $this->getServiceKernel()->createService('Question.QuestionService');
    }

    private function getHomeworkService()
    {
        return $this->getServiceKernel()->createService('Course.HomeworkService');
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}