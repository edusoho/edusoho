<?php

namespace Biz\Course\Event;

use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CourseSetSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'courseSet.maxRate.update' => 'onCourseSetMaxRateUpdate',
            'classroom.course.delete' => 'onClassroomCourseDelete',
        );
    }

    public function onCourseSetMaxRateUpdate(Event $event)
    {
        $subject = $event->getSubject();
        $courseSet = $subject['courseSet'];
        $maxRate = $subject['maxRate'];

        return $this->getCourseService()->updateMaxRateByCourseSetId($courseSet['id'], $maxRate);
    }

    public function onClassroomCourseDelete(Event $event)
    {
        $courseId = $event->getArgument('deleteCourseId');
        $course = $this->getCourseService()->getCourse($courseId);
        if (empty($course) || empty($course['parentId'])) {
            return;
        }

        $this->getCourseSetService()->deleteCourseSet($course['courseSetId']);
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
}
