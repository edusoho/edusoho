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
use Biz\Activity\Service\HomeworkActivityService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;

class HomeworkController extends BaseController
{
    public function startDoAction(Request $request, $lessonId, $homeworkId)
    {
        $activity = $this->getActivityService()->getActivity($lessonId);
        $homeworkActivity = $this->getHomeworkActivityService()->get($activity['mediaId']);
        $task = $this->getTaskService()->getTaskByCourseIdAndActivityId($activity['fromCourseId'], $activity['id']);

        $canTakeCourse = $this->getCourseService()->canTakeCourse($activity['fromCourseId']);
        if (!$canTakeCourse) {
            $this->createNewException(CourseException::FORBIDDEN_TAKE_COURSE());
        }

        return $this->forward('AppBundle:AnswerEngine/AnswerEngine:do', array(
            'answerSceneId' => $homeworkActivity['answerSceneId'],
            'assessmentId' => $homeworkActivity['assessmentId'],
        ), array(
            'submit_goto_url' => $this->generateUrl('course_task_activity_show', array('courseId' => $activity['fromCourseId'], 'id' => $task['id'])),
        ));
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

    public function showResultAction(Request $request, $answerRecordId)
    {
        if (!$this->canLookAnswerRecord($answerRecordId)) {
            $this->createNewException(CommonException::FORBIDDEN_DRAG_CAPTCHA_ERROR());
        }

        $answerRecord = $this->getAnswerRecordService()->get($answerRecordId);
        $assessment = $this->getAssessmentService()->getAssessment($answerRecord['assessment_id']);
        
        return $this->render('homework/result.html.twig', array(
            'answerRecordId' => $answerRecordId,
            'assessment' => $assessment,
        ));
    }

    protected function canLookAnswerRecord($answerRecordId)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            $this->createNewException(UserException::UN_LOGIN());
        }

        $answerRecord = $this->getAnswerRecordService()->get($answerRecordId);

        if (!$answerRecord) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }

        if ('doing' === $answerRecord['status'] && ($answerRecord['user_id'] != $user['id'])) {
            $this->createNewException(CommonException::FORBIDDEN_DRAG_CAPTCHA_ERROR());
        }

        if ($user->isAdmin()) {
            return true;
        }

        $homeworkActivity = $this->getHomeworkActivityService()->getByAnswerSceneId($answerRecord['answer_scene_id']);
        $activity = $this->getActivityService()->getByMediaIdAndMediaType($homeworkActivity['id'], 'testpaper');
        
        $course = $this->getCourseService()->getCourse($activity['fromCourseId']);
        $member = $this->getCourseMemberService()->getCourseMember($course['id'], $user['id']);

        if ('teacher' === $member['role']) {
            return true;
        }

        if ($answerRecord['user_id'] == $user['id']) {
            return true;
        }

        if ($course['parentId'] > 0) {
            $classroom = $this->getClassroomService()->getClassroomByCourseId($course['id']);
            $member = $this->getClassroomService()->getClassroomMember($classroom['id'], $user['id']);

            if ($member && array_intersect($member['role'], array('assistant', 'teacher', 'headTeacher'))) {
                return true;
            }
        }

        return false;
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

    protected function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    /**
     * @return HomeworkActivityService
     */
    protected function getHomeworkActivityService()
    {
        return $this->getBiz()->service('Activity:HomeworkActivityService');
    }

    /**
     * @return AssessmentService
     */
    protected function getAssessmentService()
    {
        return $this->createService('ItemBank:Assessment:AssessmentService');
    }

    /**
     * @return AnswerRecordService
     */
    protected function getAnswerRecordService()
    {
        return $this->createService('ItemBank:Answer:AnswerRecordService');
    }
}
