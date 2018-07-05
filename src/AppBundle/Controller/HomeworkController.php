<?php

namespace AppBundle\Controller;

use Biz\Task\Service\TaskService;
use Biz\User\Service\UserService;
use Biz\Course\Service\CourseService;
use Topxia\Service\Common\ServiceKernel;
use Biz\Activity\Service\ActivityService;
use Biz\Question\Service\QuestionService;
use Biz\Testpaper\Service\TestpaperService;
use Symfony\Component\HttpFoundation\Request;

class HomeworkController extends BaseController
{
    public function startDoAction(Request $request, $lessonId, $homeworkId)
    {
        $homework = $this->getTestpaperService()->getTestpaperByIdAndType($homeworkId, 'homework');
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

        if ('doing' === $result['status']) {
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

        $homework = $this->getTestpaperService()->getTestpaperByIdAndType($result['testId'], $result['type']);
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
            'isDone' => true,
        ));
    }

    public function showResultAction(Request $request, $resultId)
    {
        $homeworkResult = $this->getTestpaperService()->getTestpaperResult($resultId);

        $homework = $this->getTestpaperService()->getTestpaperByIdAndType($homeworkResult['testId'], $homeworkResult['type']);

        if (!$homework) {
            return $this->createMessageResponse('info', '该作业已删除，不能查看结果');
        }

        if (in_array($homeworkResult['status'], array('doing', 'paused'))) {
            return $this->redirect($this->generateUrl('homework_result_show', array('resultId' => $homeworkResult['id'])));
        }

        $canLookHomework = $this->getTestpaperService()->canLookTestpaper($homeworkResult['id']);

        if (!$canLookHomework) {
            return $this->createMessageResponse('info', '无权查看作业！');
        }

        $questions = $this->getTestpaperService()->showTestpaperItems($homework['id'], $homeworkResult['id']);

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
            return $this->createJsonResponse(array('result' => false, 'message' => 'json_response.homework_has_submitted_cannot_change.message'));
        }

        if ('POST' === $request->getMethod()) {
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
