<?php

namespace AppBundle\Controller;

use Biz\Activity\Constant\ActivityMediaType;
use Biz\Activity\Service\ActivityService;
use Biz\Activity\Service\ExerciseActivityService;
use Biz\Common\CommonException;
use Biz\Course\CourseException;
use Biz\Course\Service\CourseService;
use Biz\Task\Service\TaskService;
use Biz\Testpaper\ExerciseException;
use Biz\User\UserException;
use Codeages\Biz\ItemBank\Answer\Constant\AnswerRecordStatus;
use Codeages\Biz\ItemBank\Answer\Constant\ExerciseMode;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;
use Codeages\Biz\ItemBank\ErrorCode;
use Codeages\Biz\ItemBank\Item\Exception\ItemException;
use Symfony\Component\HttpFoundation\Request;

class ExerciseController extends BaseController
{
    public function startDoAction(Request $request, $lessonId, $exerciseId)
    {
        $activity = $this->getActivityService()->getActivity($lessonId, true);
        if (!$this->getCourseService()->canTakeCourse($activity['fromCourseId'])) {
            $this->createNewException(CourseException::FORBIDDEN_TAKE_COURSE());
        }

        try {
            $latestAnswerRecord = $this->getCurrentAnswerRecordOrStartNew($activity, $request->get('assessmentId'), $this->getCurrentUser()->getId());
        } catch (ItemException $e) {
            if (ErrorCode::ITEM_NOT_ENOUGH == $e->getCode()) {
                return $this->render('@activity/exercise/resources/views/show/index.html.twig', ['activity' => $activity, 'questionLack' => true]);
            }
        }
        if (ExerciseMode::SUBMIT_SINGLE == $latestAnswerRecord['exercise_mode']) {
            return $this->render('@activity/exercise/resources/views/show/not-support-submit-single-modal.html.twig', ['activity' => $activity]);
        }
        $task = $this->getTaskService()->getTaskByCourseIdAndActivityId($activity['fromCourseId'], $activity['id']);

        return $this->forward('AppBundle:AnswerEngine/AnswerEngine:do', [
            'answerRecordId' => $latestAnswerRecord['id'],
            'submitGotoUrl' => $this->generateUrl('course_task_activity_show', ['courseId' => $activity['fromCourseId'], 'id' => $task['id']]),
            'saveGotoUrl' => $this->generateUrl('my_course_show', ['id' => $activity['fromCourseId']]),
        ]);
    }

    public function showResultAction(Request $request, $answerRecordId)
    {
        if (!$this->canLookAnswerRecord($answerRecordId)) {
            $this->createNewException(CommonException::FORBIDDEN_DRAG_CAPTCHA_ERROR());
        }

        $answerRecord = $this->getAnswerRecordService()->get($answerRecordId);
        $assessment = $this->getAssessmentService()->getAssessment($answerRecord['assessment_id']);

        $task = $this->getTaskByAnswerSceneId($answerRecord['answer_scene_id']);
        $restartUrl = $this->generateUrl('course_task_activity_show', ['courseId' => $task['courseId'], 'id' => $task['id'], 'doAgain' => true]);

        return $this->render('exercise/result.html.twig', [
            'answerRecordId' => $answerRecordId,
            'assessment' => $assessment,
            'restartUrl' => $restartUrl,
        ]);
    }

    private function getCurrentAnswerRecordOrStartNew($activity, $assessmentId, $userId)
    {
        $latestAnswerRecord = $this->getAnswerRecordService()->getLatestAnswerRecordByAnswerSceneIdAndUserId($activity['ext']['answerSceneId'], $userId);
        if ($latestAnswerRecord && AnswerRecordStatus::FINISHED != $latestAnswerRecord['status']) {
            return $latestAnswerRecord;
        }
        if (empty($assessmentId)) {
            $assessment = $this->getExerciseActivityService()->createExerciseAssessment($activity);
            $assessmentId = $assessment['id'];
        } elseif (!$this->getExerciseActivityService()->isExerciseAssessment($assessmentId, $activity['ext'])) {
            $this->createNewException(ExerciseException::EXERCISE_NOTDO());
        }

        return $this->getAnswerService()->startAnswer($activity['ext']['answerSceneId'], $assessmentId, $userId);
    }

    protected function getTaskByAnswerSceneId($answerSceneId)
    {
        $activity = $this->getActivityService()->getActivityByAnswerSceneIdAndMediaType($answerSceneId, ActivityMediaType::EXERCISE);

        return $this->getTaskService()->getTaskByCourseIdAndActivityId($activity['fromCourseId'], $activity['id']);
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

        if (AnswerRecordStatus::DOING === $answerRecord['status'] && ($answerRecord['user_id'] != $user['id'])) {
            $this->createNewException(CommonException::FORBIDDEN_DRAG_CAPTCHA_ERROR());
        }

        if ($user->isAdmin()) {
            return true;
        }

        $activity = $this->getActivityService()->getActivityByAnswerSceneIdAndMediaType($answerRecord['answer_scene_id'], ActivityMediaType::EXERCISE);

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

            if ($member && array_intersect($member['role'], ['assistant', 'teacher', 'headTeacher'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
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

    protected function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    /**
     * @return ExerciseActivityService
     */
    protected function getExerciseActivityService()
    {
        return $this->createService('Activity:ExerciseActivityService');
    }

    /**
     * @return AnswerService
     */
    protected function getAnswerService()
    {
        return $this->createService('ItemBank:Answer:AnswerService');
    }
}
