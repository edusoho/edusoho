<?php

namespace AppBundle\Controller\Testpaper;

use Biz\Task\Service\TaskService;
use Biz\Testpaper\TestpaperException;
use Biz\User\Service\UserService;
use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\CourseService;
use AppBundle\Controller\BaseController;
use Biz\Activity\Service\ActivityService;
use Symfony\Component\HttpFoundation\Request;
use Biz\Activity\Service\TestpaperActivityService;
use Biz\Course\CourseException;
use Biz\Common\CommonException;
use Biz\User\UserException;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;

class TestpaperController extends BaseController
{
    //由于学习引擎改造，这里的 lessonId 等于 activityId
    public function doTestpaperAction(Request $request, $testId, $lessonId)
    {
        $activity = $this->getActivityService()->getActivity($lessonId);
        $testpaperActivity = $this->getTestpaperActivityService()->getActivity($activity['mediaId']);
        $task = $this->getTaskService()->getTaskByCourseIdAndActivityId($activity['fromCourseId'], $activity['id']);

        $canTakeCourse = $this->getCourseService()->canTakeCourse($activity['fromCourseId']);
        if (!$canTakeCourse) {
            $this->createNewException(CourseException::FORBIDDEN_TAKE_COURSE());
        }
        
        return $this->forward('AppBundle:AnswerEngine/AnswerEngine:do', array(
            'answerSceneId' => $testpaperActivity['answerSceneId'],
            'assessmentId' => $testpaperActivity['mediaId'],
        ), array(
            'submit_goto_url' => $this->generateUrl('course_task_activity_show', array('courseId' => $activity['fromCourseId'], 'id' => $task['id'])),
        ));
    }

    public function showResultAction(Request $request, $answerRecordId)
    {
        if (!$this->canLookAnswerRecord($answerRecordId)) {
            $this->createNewException(CommonException::FORBIDDEN_DRAG_CAPTCHA_ERROR());
        }

        $answerRecord = $this->getAnswerRecordService()->get($answerRecordId);
        $assessment = $this->getAssessmentService()->getAssessment($answerRecord['assessment_id']);
        
        return $this->render('testpaper/result.html.twig', array(
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

        $testpaperActivity = $this->getTestpaperActivityService()->getActivityByAnswerSceneId($answerRecord['answer_scene_id']);
        $activity = $this->getActivityService()->getByMediaIdAndMediaType($testpaperActivity['id'], 'testpaper');
        
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
     * @return TestpaperActivityService
     */
    protected function getTestpaperActivityService()
    {
        return $this->createService('Activity:TestpaperActivityService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
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
