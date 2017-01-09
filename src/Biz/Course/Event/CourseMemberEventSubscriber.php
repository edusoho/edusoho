<?php

namespace Biz\Course\Event;

use Biz\Course\Service\CourseService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Biz\Course\Service\CourseSetService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CourseMemberEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'course.join' => 'onMemberCreate',
            'course.quit' => 'onMemberDelete'
        );
    }
    
    public function onMemberCreate(Event $event)
    {
        $course = $event->getSubject();
        $member = $event->getArgument('member');

        if ($member['role'] == 'student') {
            $this->getCourseService()->updateCourseStatistics($course['id'], array('studentNum'));
            $this->getCourseSetService()->updateCourseSetStatistics($course['courseSetId'], array('studentNum'));
        }
    }

    public function onMemberDelete(Event $event)
    {
        $course = $event->getSubject();
        $member = $event->getArgument('member');

        if ($member['role'] == 'student') {
            $this->getCourseService()->updateCourseStatistics($course['id'], array('studentNum'));
            $this->getCourseSetService()->updateCourseSetStatistics($course['courseSetId'], array('studentNum'));
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
}