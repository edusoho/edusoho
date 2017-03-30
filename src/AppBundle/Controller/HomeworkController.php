<?php

namespace AppBundle\Controller;

use Biz\Activity\Service\ActivityService;
use Biz\Course\Service\CourseService;
use Biz\Question\Service\QuestionService;
use Biz\Task\Service\TaskService;
use Biz\Testpaper\Service\TestpaperService;
use Biz\User\Service\UserService;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;

class HomeworkController extends BaseController
{
    public function startDoAction(Request $request, $lessonId, $homeworkId)
    {
        $homework = $this->getTestpaperService()->getTestpaper($homeworkId);
        if (empty($homework)) {
            return $this->createMessageResponse('info', 'homework not found');
        }

        $activity = $this->getActivityService()->getActivity($lessonId);
        if ($activity['mediaId'] != $homeworkId) {
            //homeworkId not belong to activity(lessonId)
            return $this->createMessageResponse('info', "homework#{$homeworkId} not found in activity#{$lessonId}");
        }

        list($course, $member) = $this->getCourseService()->tryTakeCourse($activity['fromCourseId']);

        $result = $this->getTestpaperService()->startTestpaper($homeworkId, array('lessonId' => $lessonId, 'courseId' => $course['id']));

        if ($result['status'] === 'doing') {
            return $this->redirect($this->generateUrl('homework_show', array(
                'resultId' => $result['id'],
            )));
        }

        return $this->redirect($this->generateUrl('homework_result_show', array(
            'resultId' => $result['id'],
        )));
    }

    public function doTestAction(Request $request, $resultId)
    {
        $result = $this->getTestpaperService()->getTestpaperResult($resultId);
        if (!$result) {
            return $this->createMessageResponse('info', 'homework result not found');
        }

        list($course, $member) = $this->getCourseService()->tryTakeCourse($result['courseId']);

        $homework = $this->getTestpaperService()->getTestpaper($result['testId']);
        if (!$homework) {
            return $this->createMessageResponse('info', 'homework not found');
        }

        $questions = $this->getTestpaperService()->showTestpaperItems($homework['id'], $result['id']);

        $activity = $this->getActivityService()->getActivity($result['lessonId']);

        return $this->render('homework/do.html.twig', array(
            'paper' => $homework,
            'questions' => $questions,
            'course' => $course,
            'paperResult' => $result,
            'activity' => $activity,
            'showTypeBar' => 0,
            'showHeader' => 0,
        ));
    }

    public function showResultAction(Request $request, $resultId)
    {
        $homeworkResult = $this->getTestpaperService()->getTestpaperResult($resultId);

        $homework = $this->getTestpaperService()->getTestpaper($homeworkResult['testId']);

        if (!$homework) {
            throw $this->createResourceNotFoundException('homework', $homeworkResult['testId']);
        }

        if (in_array($homeworkResult['status'], array('doing', 'paused'))) {
            return $this->redirect($this->generateUrl('homework_result_show', array('resultId' => $homeworkResult['id'])));
        }

        $canLookHomework = $this->getTestpaperService()->canLookTestpaper($homeworkResult['id']);

        if (!$canLookHomework) {
            return $this->createMessageResponse('info', '无权查看作业！');
        }

        $builder = $this->getTestpaperService()->getTestpaperBuilder($homework['type']);
        $questions = $builder->showTestItems($homework['id'], $homeworkResult['id']);

        $student = $this->getUserService()->getUser($homeworkResult['userId']);

        $attachments = $this->getTestpaperService()->findAttachments($homework['id']);

        $activity = $this->getActivityService()->getActivity($homeworkResult['lessonId']);
        $task = $this->getTaskService()->getTaskByCourseIdAndActivityId($activity['fromCourseId'], $activity['id']);

        return $this->render('homework/do.html.twig', array(
            'questions' => $questions,
            'paper' => $homework,
            'paperResult' => $homeworkResult,
            'student' => $student,
            'attachments' => $attachments,
            'task' => $task,
            'action' => $request->query->get('action', ''),
        ));
    }

    public function submitAction(Request $request, $resultId)
    {
        $result = $this->getTestpaperService()->getTestpaperResult($resultId);

        if (!empty($result) && !in_array($result['status'], array('doing', 'paused'))) {
            return $this->createJsonResponse(array('result' => false, 'message' => '作业已提交，不能再修改答案！'));
        }

        if ($request->getMethod() === 'POST') {
            $formData = $request->request->all();

            $paperResult = $this->getTestpaperService()->finishTest($result['id'], $formData);

            $goto = $this->generateUrl('homework_result_show', array('resultId' => $paperResult['id']));

            return $this->createJsonResponse(array('result' => true, 'message' => '', 'goto' => $goto));
        }

        return $this->createJsonResponse(array('result' => false, 'message' => 'result not found'));
    }

    /**
     * @return TestpaperService
     */
    protected function getTestpaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
    }

    /**
     * @return QuestionService
     */
    protected function getQuestionService()
    {
        return $this->createService('Question:QuestionService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return ServiceKernel
     */
    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}
