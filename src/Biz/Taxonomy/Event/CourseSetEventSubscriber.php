<?php

namespace Biz\Taxonomy\Event;

use Biz\Taxonomy\TagOwnerManager;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CourseSetEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'course-set.update' => 'onCourseSetUpdate',
        );
    }

    public function onCourseSetUpdate(Event $event)
    {
        $courseSet = $event->getSubject();
        $ownerManager = new TagOwnerManager('course-set', $courseSet['id'], $courseSet['tags'], $courseSet['creator']);

        if (empty($courseSet['tags'])) {
            $ownerManager->delete();

            return;
        }

        $ownerManager->update();
    }
}
