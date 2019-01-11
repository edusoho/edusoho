<?php

namespace Biz\User\Event;

use AppBundle\Common\StringToolkit;
use Biz\User\CurrentUser;
use Biz\User\Service\StatusService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class StatusEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    /**
     * @return mixed
     */
    public static function getSubscribedEvents()
    {
        return array(
            'course.task.start' => 'onCourseTaskStart',
            'course.task.finish' => 'onCourseTaskFinish',
            'exam.reviewed' => 'onTestpaperReviewed',
        );
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

        $this->getStatusService()->publishStatus(array(
            'type' => 'task_start',
            'courseId' => $course['id'],
            'classroomId' => $classroom ? $classroom['id'] : 0,
            'objectType' => 'task',
            'objectId' => $task['id'],
            'private' => $isPrivate,
            'properties' => array(
                'course' => $this->simplifyCousrse($course),
                'task' => $this->simplifyTask($task),
            ),
        ));
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

        $this->getStatusService()->publishStatus(array(
            'type' => 'task_finish',
            'courseId' => $course['id'],
            'classroomId' => $classroom ? $classroom['id'] : 0,
            'objectType' => 'task',
            'objectId' => $task['id'],
            'private' => $isPrivate,
            'properties' => array(
                'course' => $this->simplifyCousrse($course),
                'task' => $this->simplifyTask($task),
            ),
            'userId' => $user['id'],
        ));
    }

    public function onTestpaperReviewed(Event $event)
    {
        $paperResult = $event->getSubject();

        $course = $this->getCourseService()->getCourse($paperResult['courseId']);
        $activity = $this->getActivityService()->getActivity($paperResult['lessonId']);
        $testpaper = $this->getTestpaperService()->getTestpaperByIdAndType($paperResult['testId'], $paperResult['type']);

        if (!$course || !$activity || !$testpaper) {
            return;
        }

        list($classroom, $isPrivate) = $this->isPrivate($course);

        $type = "reviewed_{$paperResult['type']}";

        $this->getStatusService()->publishStatus(array(
            'userId' => $paperResult['userId'],
            'courseId' => $course['id'],
            'classroomId' => $classroom ? $classroom['id'] : 0,
            'type' => $type,
            'objectType' => $testpaper['type'],
            'objectId' => $testpaper['id'],
            'private' => $isPrivate,
            'properties' => array(
                'testpaper' => $this->simplifyTestpaper($testpaper),
                'result' => $this->simplifyTestpaperResult($paperResult),
                'activity' => $this->simplifyActivity($activity),
                'version' => '2.0',
            ),
        ));
    }

    protected function simplifyCousrse($course)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);

        return array(
            'id' => $course['id'],
            'courseSetId' => $course['courseSetId'],
            'title' => $course['title'],
            'picture' => $courseSet['cover'],
            'type' => $course['type'],
            'rating' => $course['rating'],
            'about' => StringToolkit::plain($course['summary'], 100),
            'price' => $course['price'],
        );
    }

    protected function simplifyTask($task)
    {
        return array(
            'id' => $task['id'],
            'number' => $task['number'],
            'type' => $task['type'],
            'title' => $task['title'],
            'summary' => '',
        );
    }

    protected function simplifyTestpaper($testpaper)
    {
        return array(
            'id' => $testpaper['id'],
            'name' => $testpaper['name'],
            'description' => StringToolkit::plain($testpaper['description'], 100),
            'score' => $testpaper['score'],
            'passedScore' => $testpaper['passedCondition'],
            'itemCount' => $testpaper['itemCount'],
        );
    }

    protected function simplifyTestpaperResult($testpaperResult)
    {
        return array(
            'id' => $testpaperResult['id'],
            'userId' => $testpaperResult['userId'],
            'score' => $testpaperResult['score'],
            'objectiveScore' => $testpaperResult['objectiveScore'],
            'subjectiveScore' => $testpaperResult['subjectiveScore'],
            'teacherSay' => StringToolkit::plain($testpaperResult['teacherSay'], 100),
            'passedStatus' => $testpaperResult['passedStatus'],
        );
    }

    protected function simplifyActivity($activity)
    {
        return array(
            'id' => $activity['id'],
            'type' => $activity['mediaType'],
            'title' => $activity['title'],
            'summary' => StringToolkit::plain($activity['content'], 100),
        );
    }

    protected function isPrivate($course)
    {
        $private = $course['status'] == 'published' ? 0 : 1;
        $classroom = array();

        if ($course['parentId']) {
            $classroom = $this->getClassroomService()->getClassroomByCourseId($course['id']);

            if (array_key_exists('showable', $classroom) && $classroom['showable'] == 1) {
                $private = 0;
            } else {
                $private = 1;
            }
        }

        return array($classroom, $private);
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
