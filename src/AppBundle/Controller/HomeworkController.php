<?php
namespace AppBundle\Controller;

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Exception\ResourceNotFoundException;

class HomeworkController extends BaseController
{
    public function startDoAction(Request $request, $lessonId, $homeworkId)
    {
        $homework = $this->getTestpaperService()->getTestpaper($homeworkId);
        if (empty($homework)) {
            throw new ResourceNotFoundException('homework', $homeworkId);
        }

        list($course, $member) = $this->getCourseService()->tryTakeCourse($homework['courseId']);

        $result = $this->getTestpaperService()->startTestpaper($homeworkId, array('lessonId' => $lessonId, 'courseId' => $course['id']));

        if ($result['status'] == 'doing') {
            return $this->redirect($this->generateUrl('homework_show', array(
                'resultId' => $result['id']
            )));
        } else {
            return $this->redirect($this->generateUrl('homework_result_show', array(
                'resultId' => $result['id']
            )));
        }
    }

    public function doTestAction(Request $request, $resultId)
    {
        $result = $this->getTestpaperService()->getTestpaperResult($resultId);
        if (!$result) {
            throw new ResourceNotFoundException('homeworkResult', $resultId);
        }

        list($course, $member) = $this->getCourseService()->tryTakeCourse($result['courseId']);

        $homework = $this->getTestpaperService()->getTestpaper($result['testId']);
        if (!$homework) {
            throw new ResourceNotFoundException('homework', $result['testId']);
        }

        $questions = $this->getTestpaperService()->showTestpaperItems($homework['id'], $result['id']);

        $activity = $this->getActivityService()->getActivity($result['lessonId']);

        return $this->render('homework/do.html.twig', array(
            'paper'       => $homework,
            'questions'   => $questions,
            'course'      => $course,
            'paperResult' => $result,
            'activity'    => $activity,
            'showTypeBar' => 0,
            'showHeader'  => 0
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
            return $this->redirect($this->generateUrl('homework_result_show', array('resultId' => $testpaperResult['id'])));
        }

        $canLookHomework = $this->getTestpaperService()->canLookTestpaper($homeworkResult['id']);

        if (!$canLookHomework) {
            throw new AccessDeniedException($this->getServiceKernel()->trans('无权查看作业！'));
        }

        $builder   = $this->getTestpaperService()->getTestpaperBuilder($homework['type']);
        $questions = $builder->showTestItems($homework['id'], $homeworkResult['id']);

        $student = $this->getUserService()->getUser($homeworkResult['userId']);

        $attachments = $this->getTestpaperService()->findAttachments($homework['id']);

        $activity = $this->getActivityService()->getActivity($homeworkResult['lessonId']);
        $task     = $this->getTaskService()->getTaskByCourseIdAndActivityId($activity['fromCourseId'], $activity['id']);

        return $this->render('homework/do.html.twig', array(
            'questions'   => $questions,
            'paper'       => $homework,
            'paperResult' => $homeworkResult,
            'student'     => $student,
            'attachments' => $attachments,
            'task'        => $task,
            'action'      => $request->query->get('action', '')
        ));
    }

    public function submitAction(Request $request, $resultId)
    {
        $result = $this->getTestpaperService()->getTestpaperResult($resultId);

        if (!empty($result) && !in_array($result['status'], array('doing', 'paused'))) {
            return $this->createJsonResponse(array('result' => false, 'message' => '作业已提交，不能再修改答案！'));
        }

        if ($request->getMethod() == 'POST') {
            $formData = $request->request->all();

            $paperResult = $this->getTestpaperService()->finishTest($result['id'], $formData);

            $goto = $this->generateUrl('homework_result_show', array('resultId' => $paperResult['id']));

            return $this->createJsonResponse(array('result' => true, 'message' => '', 'goto' => $goto));
        }
    }

    protected function getTestpaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
    }

    protected function getQuestionService()
    {
        return $this->createService('Question:QuestionService');
    }

    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}
