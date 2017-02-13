<?php

namespace Biz\Course\Event;

use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CourseSetSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'course-set.maxRate.update'          => 'onCourseSetMaxRateUpdate',
        );
    }

    public function onCourseSetMaxRateUpdate(Event $event)
    {
        $subject = $event->getSubject();
        $courseSet = $subject['courseSet'];
        $maxRate = $subject['maxRate'];
        return $this->getCourseService()->updateMaxRateByCourseSetId($courseSet['id'], $maxRate);
    }

    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }
}
