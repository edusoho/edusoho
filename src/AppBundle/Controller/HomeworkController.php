<?php

namespace AppBundle\Controller;

use Biz\Activity\Service\ActivityService;
use Biz\Activity\Service\HomeworkActivityService;
use Biz\Common\CommonException;
use Biz\Course\Exception\CourseException;
use Biz\Course\Service\CourseService;
use Biz\Task\Service\TaskService;
use Biz\Testpaper\Service\TestpaperService;
use Biz\User\Service\UserService;
use Biz\User\UserException;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerReportService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\ServiceKernel;

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

        return $this->forward('AppBundle:AnswerEngine/AnswerEngine:do', [
            'answerSceneId' => $homeworkActivity['answerSceneId'],
            'assessmentId' => $homeworkActivity['assessmentId'],
        ], [
            'submit_goto_url' => $this->generateUrl('course_task_activity_show', ['courseId' => $activity['fromCourseId'], 'id' => $task['id']]),
            'save_goto_url' => $this->generateUrl('my_course_show', ['id' => $activity['fromCourseId']]),
        ]);
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

        return $this->render('homework/do.html.twig', [
            'paper' => $homework,
            'questions' => $questions,
            'course' => $course,
            'paperResult' => $result,
            'activity' => $activity,
            'showTypeBar' => 0,
            'showHeader' => 0,
            'isDone' => true,
        ]);
    }

    public function showResultAction(Request $request, $answerRecordId)
    {
        if (!$this->canLookAnswerRecord($answerRecordId)) {
            $this->createNewException(CommonException::FORBIDDEN_DRAG_CAPTCHA_ERROR());
        }

        $answerRecord = $this->getAnswerRecordService()->get($answerRecordId);
        $answerReport = $this->getAnswerReportService()->get($answerRecord['answer_report_id']);
        $assessment = $this->getAssessmentService()->getAssessment($answerRecord['assessment_id']);

        if ('my' == $request->query->get('action', '')) {
            $task = $this->getTaskByAnswerSceneId($answerRecord['answer_scene_id']);
            $restartUrl = $this->generateUrl('course_task_show', ['id' => $task['id'], 'courseId' => $task['courseId']]);
        } elseif ('' == $request->query->get('action', '')) {
            $restartUrl = $this->generateUrl('homework_start_do', ['lessonId' => $this->getActivityIdByAnswerSceneId($answerRecord['answer_scene_id']), 'homeworkId' => 1]);
        } else {
            $restartUrl = '';
        }

        return $this->render('homework/result.html.twig', [
            'answerReport' => $answerReport,
            'answerRecord' => $answerRecord,
            'answerRecordId' => $answerRecordId,
            'assessment' => $assessment,
            'restartUrl' => $restartUrl,
        ]);
    }

    protected function getTaskByAnswerSceneId($answerSceneId)
    {
        $homeworkActivity = $this->getHomeworkActivityService()->getByAnswerSceneId($answerSceneId);
        $activity = $this->getActivityService()->getByMediaIdAndMediaType($homeworkActivity['id'], 'homework');

        return $this->getTaskService()->getTaskByCourseIdAndActivityId($activity['fromCourseId'], $activity['id']);
    }

    protected function getActivityIdByAnswerSceneId($answerSceneId)
    {
        $homeworkActivity = $this->getHomeworkActivityService()->getByAnswerSceneId($answerSceneId);

        return $this->getActivityService()->getByMediaIdAndMediaType($homeworkActivity['id'], 'homework')['id'];
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
        $activity = $this->getActivityService()->getByMediaIdAndMediaType($homeworkActivity['id'], 'homework');

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
     * @return TestpaperService
     */
    protected function getTestpaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
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

    /**
     * @return AnswerReportService
     */
    protected function getAnswerReportService()
    {
        return $this->createService('ItemBank:Answer:AnswerReportService');
    }

    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }
}
