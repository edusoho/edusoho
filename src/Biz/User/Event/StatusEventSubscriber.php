<?php

namespace Biz\User\Event;

use AppBundle\Common\StringToolkit;
use Biz\User\CurrentUser;
use Biz\User\Service\StatusService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class StatusEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    /**
     * @return mixed
     */
    public static function getSubscribedEvents()
    {
        return [
            'course.task.start' => 'onCourseTaskStart',
            'course.task.finish' => 'onCourseTaskFinish',
            'answer.finished' => 'onAnswerFinished',
        ];
    }

    public function onCourseTaskStart(Event $event)
    {
        $user = $this->getCurrentUser();
        if (empty($user) || !$user->isLogin()) {
            return;
        }

        $taskResult = $event->getSubject();
        $course = $this->getCourseService()->getCourse($taskResult['courseId']);
        if (empty($course) || !$this->getMemberService()->isCourseStudent($course['id'], $user['id'])) {
            return;
        }

        $task = $this->getTaskService()->getTask($taskResult['courseTaskId']);

        if (empty($task)) {
            return;
        }

        list($classroom, $isPrivate) = $this->isPrivate($course);

        $this->getStatusService()->publishStatus([
            'type' => 'task_start',
            'courseId' => $course['id'],
            'classroomId' => $classroom ? $classroom['id'] : 0,
            'objectType' => 'task',
            'objectId' => $task['id'],
            'private' => $isPrivate,
            'properties' => [
                'course' => $this->simplifyCousrse($course),
                'task' => $this->simplifyTask($task),
            ],
        ]);
    }

    public function onCourseTaskFinish(Event $event)
    {
        $user = $event->getArgument('user');
        if (empty($user)) {
            return;
        }

        if ($user instanceof CurrentUser && !$user->isLogin()) {
            return;
        }

        $taskResult = $event->getSubject();
        $course = $this->getCourseService()->getCourse($taskResult['courseId']);

        if (empty($course) || !$this->getMemberService()->isCourseStudent($course['id'], $user['id'])) {
            return;
        }

        $task = $this->getTaskService()->getTask($taskResult['courseTaskId']);

        if (empty($task)) {
            return;
        }

        list($classroom, $isPrivate) = $this->isPrivate($course);

        $this->getStatusService()->publishStatus([
            'type' => 'task_finish',
            'courseId' => $course['id'],
            'classroomId' => $classroom ? $classroom['id'] : 0,
            'objectType' => 'task',
            'objectId' => $task['id'],
            'private' => $isPrivate,
            'properties' => [
                'course' => $this->simplifyCousrse($course),
                'task' => $this->simplifyTask($task),
            ],
            'userId' => $user['id'],
        ]);
    }

    public function onAnswerFinished(Event $event)
    {
        $answerReport = $event->getSubject();
        $answerRecord = $this->getAnswerRecordService()->get($answerReport['answer_record_id']);
        $activity = $this->getActivityService()->getActivityByAnswerSceneId($answerReport['answer_scene_id']);

        $course = $this->getCourseService()->getCourse($activity['fromCourseId']);
        $assessment = $this->getAssessmentService()->getAssessment($answerRecord['assessment_id']);

        list($classroom, $isPrivate) = $this->isPrivate($course);

        $type = "reviewed_{$activity['mediaType']}";

        $this->getStatusService()->publishStatus([
            'userId' => $answerRecord['user_id'],
            'courseId' => $course['id'],
            'classroomId' => $classroom ? $classroom['id'] : 0,
            'type' => $type,
            'objectType' => $activity['mediaType'],
            'objectId' => $assessment['id'],
            'private' => $isPrivate,
            'properties' => [
                'testpaper' => $this->simplifyTestpaper($assessment),
                'result' => $this->simplifyTestpaperResult($activity, $answerRecord, $answerReport),
                'activity' => $this->simplifyActivity($activity),
                'version' => '2.0',
            ],
        ]);
    }

    protected function simplifyCousrse($course)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);

        return [
            'id' => $course['id'],
            'courseSetId' => $course['courseSetId'],
            'title' => $course['title'],
            'picture' => $courseSet['cover'],
            'type' => $course['type'],
            'rating' => $course['rating'],
            'about' => StringToolkit::plain($course['summary'], 100),
            'price' => $course['price'],
        ];
    }

    protected function simplifyTask($task)
    {
        return [
            'id' => $task['id'],
            'number' => $task['number'],
            'type' => $task['type'],
            'title' => $task['title'],
            'summary' => '',
        ];
    }

    protected function simplifyTestpaper($assessment)
    {
        return [
            'id' => $assessment['id'],
            'name' => $assessment['name'],
            'description' => StringToolkit::plain($assessment['description'], 100),
            'score' => $assessment['total_score'],
            'passedScore' => ['type' => 'submit'],
            'itemCount' => $assessment['question_count'],
        ];
    }

    protected function simplifyTestpaperResult($activity, $answerRecord, $answerReport)
    {
        if ('testpaper' == $activity['mediaType']) {
            $answerScene = $this->getAnswerSceneService()->get($answerReport['answer_scene_id']);
            $passedStatus = $answerReport['score'] >= $answerScene['pass_score'] ? 'passed' : 'unpassed';
        }

        return [
            'id' => $answerRecord['id'],
            'userId' => $answerRecord['user_id'],
            'score' => $answerReport['score'],
            'objectiveScore' => $answerReport['objective_score'],
            'subjectiveScore' => $answerReport['subjective_score'],
            'teacherSay' => StringToolkit::plain($answerReport['comment'], 100),
            'passedStatus' => !empty($passedStatus) ? $passedStatus : $answerReport['grade'],
        ];
    }

    protected function simplifyActivity($activity)
    {
        return [
            'id' => $activity['id'],
            'type' => $activity['mediaType'],
            'title' => $activity['title'],
            'summary' => StringToolkit::plain($activity['content'], 100),
        ];
    }

    protected function isPrivate($course)
    {
        $private = 'published' == $course['status'] ? 0 : 1;
        $classroom = [];

        if ($course['parentId']) {
            $classroom = $this->getClassroomService()->getClassroomByCourseId($course['id']);

            if (array_key_exists('showable', $classroom) && 1 == $classroom['showable']) {
                $private = 0;
            } else {
                $private = 1;
            }
        }

        return [$classroom, $private];
    }

    protected function getCurrentUser()
    {
        $biz = $this->getBiz();

        return $biz['user'];
    }

    protected function getMemberService()
    {
        return $this->getBiz()->service('Course:MemberService');
    }

    /**
     * @return AssessmentService
     */
    public function getAssessmentService()
    {
        return $this->getBiz()->service('ItemBank:Assessment:AssessmentService');
    }

    /**
     * @return AnswerRecordService
     */
    public function getAnswerRecordService()
    {
        return $this->getBiz()->service('ItemBank:Answer:AnswerRecordService');
    }

    /**
     * @return AnswerSceneService
     */
    public function getAnswerSceneService()
    {
        return $this->getBiz()->service('ItemBank:Answer:AnswerSceneService');
    }

    /**
     * @return StatusService
     */
    protected function getStatusService()
    {
        return $this->getBiz()->service('User:StatusService');
    }

    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    protected function getCourseSetService()
    {
        return $this->getBiz()->service('Course:CourseSetService');
    }

    protected function getTaskService()
    {
        return $this->getBiz()->service('Task:TaskService');
    }

    protected function getClassroomService()
    {
        return $this->getBiz()->service('Classroom:ClassroomService');
    }

    public function getTestpaperService()
    {
        return $this->getBiz()->service('Testpaper:TestpaperService');
    }

    public function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }
}
