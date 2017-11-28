<?php

namespace AppBundle\Controller;

use Biz\User\Service\UserService;
use Biz\Course\Service\CourseService;
use Topxia\Service\Common\ServiceKernel;
use Biz\Activity\Service\ActivityService;
use Biz\Question\Service\QuestionService;
use Biz\Testpaper\Service\TestpaperService;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\Exception\ResourceNotFoundException;

class ExerciseController extends BaseController
{
    public function startDoAction(Request $request, $lessonId, $exerciseId)
    {
        $exercise = $this->getTestpaperService()->getTestpaperByIdAndType($exerciseId, 'exercise');
        if (empty($exercise)) {
            throw new ResourceNotFoundException('exercise', $exerciseId);
        }

        $activity = $this->getActivityService()->getActivity($lessonId);
        if ($activity['mediaId'] != $exerciseId) {
            //exerciseId not belong to activity(lessonId)
            throw new ResourceNotFoundException('exercise', $exerciseId);
        }

        list($course, $member) = $this->getCourseService()->tryTakeCourse($activity['fromCourseId']);

        $result = $this->getTestpaperService()->startTestpaper($exercise['id'], array('lessonId' => $lessonId, 'courseId' => $course['id']));

        if ($result['status'] == 'doing') {
            return $this->redirect($this->generateUrl('exercise_show', array(
                'resultId' => $result['id'],
            )));
        } else {
            return $this->redirect($this->generateUrl('exercise_result_show', array(
                'resultId' => $result['id'],
            )));
        }
    }

    public function doTestAction(Request $request, $resultId)
    {
        $result = $this->getTestpaperService()->getTestpaperResult($resultId);
        if (!$result) {
            throw new ResourceNotFoundException('exerciseResult', $resultId);
        }

        list($course, $member) = $this->getCourseService()->tryTakeCourse($result['courseId']);

        $exercise = $this->getTestpaperService()->getTestpaperByIdAndType($result['testId'], 'exercise');
        if (!$exercise) {
            throw new ResourceNotFoundException('exercise', $result['testId']);
        }

        $questions = $this->getTestpaperService()->showTestpaperItems($exercise['id'], $result['id']);

        $activity = $this->getActivityService()->getActivity($result['lessonId']);

        $exercise['itemCount'] = $this->getActureQuestionNum($questions);

        return $this->render('exercise/do.html.twig', array(
            'paper' => $exercise,
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
        $exerciseResult = $this->getTestpaperService()->getTestpaperResult($resultId);

        $exercise = $this->getTestpaperService()->getTestpaperByIdAndType($exerciseResult['testId'], $exerciseResult['type']);

        if (!$exercise) {
            throw $this->createResourceNotFoundException('exercise', $exerciseResult['testId']);
        }

        if (in_array($exerciseResult['status'], array('doing', 'paused'))) {
            return $this->redirect($this->generateUrl('exercise_show', array('resultId' => $exerciseResult['id'])));
        }

        $canLookExercise = $this->getTestpaperService()->canLookTestpaper($exerciseResult['id']);

        if (!$canLookExercise) {
            throw $this->createAccessDeniedException('无权查看作业！');
        }

        $builder = $this->getTestpaperService()->getTestpaperBuilder($exercise['type']);
        $questions = $builder->showTestItems($exercise['id'], $exerciseResult['id']);

        $seq = $request->query->get('seq', '');

        $questions = $this->sortQuestions($questions, $seq);

        $student = $this->getUserService()->getUser($exerciseResult['userId']);

        $attachments = $this->getTestpaperService()->findAttachments($exercise['id']);

        $exercise['itemCount'] = $this->getActureQuestionNum($questions);

        return $this->render('exercise/do.html.twig', array(
            'questions' => $questions,
            'paper' => $exercise,
            'paperResult' => $exerciseResult,
            'student' => $student,
            'attachments' => $attachments,
            'action' => $request->query->get('action', ''),
        ));
    }

    public function submitAction(Request $request, $resultId)
    {
        $result = $this->getTestpaperService()->getTestpaperResult($resultId);

        if (!empty($result) && !in_array($result['status'], array('doing', 'paused'))) {
            return $this->createJsonResponse(array('result' => false, 'message' => 'json_response.exercise_cannot_submit_answer.message'));
        }

        if ($request->getMethod() === 'POST') {
            $formData = $request->request->all();

            $paperResult = $this->getTestpaperService()->finishTest($result['id'], $formData);

            $goto = $this->generateUrl('exercise_result_show', array('seq' => $formData['seq'], 'resultId' => $paperResult['id']));

            return $this->createJsonResponse(array('result' => true, 'message' => '', 'goto' => $goto));
        }

        return $this->createJsonResponse(array('result' => false, 'message' => 'result not found'));
    }

    protected function getActureQuestionNum($questions)
    {
        return array_reduce($questions, function ($count, $question) {
            if ($question['type'] == 'material' && isset($question['subs'])) {
                $count += count($question['subs']);
            } else {
                $count += 1;
            }

            return $count;
        }, 0);
    }

    private function sortQuestions($questions, $order)
    {
        usort($questions, function ($a, $b) use ($order) {
            $pos_a = array_search($a['id'], $order);
            $pos_b = array_search($b['id'], $order);

            return $pos_a - $pos_b;
        });

        return $questions;
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
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return ServiceKernel
     */
    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}
