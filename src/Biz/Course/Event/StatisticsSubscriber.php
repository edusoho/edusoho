<?php

namespace Biz\Course\Event;

use AppBundle\Common\ArrayToolkit;
use Biz\Task\Service\TaskResultService;
use Biz\Task\Service\TaskService;
use Biz\System\Service\LogService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Codeages\Biz\Framework\Event\Event;
use Biz\Course\Service\CourseSetService;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class StatisticsSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'course.task.create' => 'onTaskCreate',
            'course.task.update' => 'onTaskUpdate',
            'course.task.delete' => 'onTaskDelete',
            //'course.task.publish' => 'onPublishTaskNumberChange',
            //'course.task.unpublish' => 'onPublishTaskNumberChange',

            'course.lesson.publish' => array('onPublishLessonNumberChange', -100),
            'course.lesson.unpublish' => array('onPublishLessonNumberChange', -100),
            'course.lesson.create' => array('onLessonNumberChange', -100),
            'course.lesson.delete' => array('onLessonNumberChange', -100),
            'course.lesson.setOptional' => array('onLessonOptionalChange', -100),

            'course.thread.create' => 'onCourseThreadChange',
            'course.thread.delete' => 'onCourseThreadChange',

            'course.review.add' => 'onReviewNumberChange',
            'course.review.update' => 'onReviewNumberChange',
            'course.review.delete' => 'onReviewNumberChange',

            'course.marketing.update' => 'onCourseMarketingChange',
            'course.publish' => 'onCourseStatusChange',
            'course.close' => 'onCourseStatusChange',
            'course.delete' => 'onCourseDelete',
        );
    }

    public function onCourseMarketingChange(Event $event)
    {
        $subject = $event->getSubject();
        $course = $subject['newCourse'];
        $this->getCourseSetService()->updateCourseSetMinAndMaxPublishedCoursePrice($course['courseSetId']);
        $this->updateCopiedCourseSetPrice($course['courseSetId']);
    }

    public function onCourseStatusChange(Event $event)
    {
        $course = $event->getSubject();
        $this->getCourseSetService()->updateCourseSetMinAndMaxPublishedCoursePrice($course['courseSetId']);
    }

    public function onTaskCreate(Event $event)
    {
        $this->onTaskNumberChange($event, array('taskNum', 'compulsoryTaskNum'));
    }

    public function onTaskUpdate(Event $event)
    {
        $newTask = $event->getSubject();
        $oldTask = $event->getArguments();
        $isOptionalChange = isset($oldTask['isOptional']) && $newTask['isOptional'] != $oldTask['isOptional'];
        if ($isOptionalChange) {
            $this->onTaskNumberChange($event, array('taskNum', 'compulsoryTaskNum'));
        }
    }

    public function onTaskDelete(Event $event)
    {
        $task = $event->getSubject();
        $this->getTaskResultService()->deleteTaskResultsByTaskId($task['id']);
        $this->onTaskNumberChange($event, array('taskNum', 'compulsoryTaskNum'));
    }

    public function onPublishTaskNumberChange(Event $event)
    {
        $task = $event->getSubject();
        $this->getCourseService()->updateCourseStatistics($task['courseId'], array(
            'compulsoryTaskNum',
        ));
    }

    public function onPublishLessonNumberChange(Event $event)
    {
        $lesson = $event->getSubject();
        $this->getCourseService()->updateCourseStatistics($lesson['courseId'], array(
            'compulsoryTaskNum', 'publishLessonNum',
        ));
    }

    public function onLessonNumberChange(Event $event)
    {
        $lesson = $event->getSubject();
        $this->getCourseService()->updateCourseStatistics($lesson['courseId'], array(
            'lessonNum', 'publishLessonNum',
        ));
    }

    public function onCourseThreadChange(Event $event)
    {
        $thread = $event->getSubject();
        $this->getCourseService()->updateCourseStatistics($thread['courseId'], array(
            $thread['type'].'Num',
        ));
    }

    public function onReviewNumberChange(Event $event)
    {
        $review = $event->getSubject();

        $this->getCourseService()->updateCourseStatistics($review['courseId'], array(
            'ratingNum',
        ));
    }

    public function onCourseDelete(Event $event)
    {
        $course = $event->getSubject();

        $this->getCourseSetService()->updateCourseSetStatistics($course['courseSetId'], array('ratingNum', 'noteNum', 'studentNum', 'materialNum'));
    }

    public function onLessonOptionalChange(Event $event)
    {
        $lesson = $event->getSubject();

        $this->getCourseService()->updateCourseStatistics($lesson['courseId'], array('compulsoryTaskNum'));
    }

    protected function onTaskNumberChange(Event $event, $fields)
    {
        $task = $event->getSubject();
        $this->getCourseService()->updateCourseStatistics($task['courseId'], $fields);
    }

    protected function updateCopiedCourseSetPrice($courseSetId)
    {
        $copiedCourseSets = $this->getCourseSetService()->findCourseSetsByParentIdAndLocked($courseSetId, 1);
        $copiedCourseSetIds = ArrayToolkit::column($copiedCourseSets, 'id');

        foreach ($copiedCourseSetIds as $copiedCourseSetId) {
            $this->getCourseSetService()->updateCourseSetMinAndMaxPublishedCoursePrice($copiedCourseSetId);
        }
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->getBiz()->service('Course:CourseSetService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->getBiz()->service('System:LogService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->getBiz()->service('Task:TaskService');
    }

    /**
     * @return TaskResultService
     */
    protected function getTaskResultService()
    {
        return $this->getBiz()->service('Task:TaskResultService');
    }

    /**
     * @return MemberService
     */
    protected function getMemberService()
    {
        return $this->getBiz()->service('Course:MemberService');
    }
}
