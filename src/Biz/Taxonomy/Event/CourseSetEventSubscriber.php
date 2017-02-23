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
            'course-set.update' => 'onCourseSetUpdate'
        );
    }

    public function onCourseSetUpdate(Event $event)
    {
        $courseSet = $event->getSubject();
        if(empty($courseSet['tags'])){
            return;
        }

        $ownerManager = new TagOwnerManager('course-set', $courseSet['id'], $courseSet['tags'], $courseSet['creator']);
        $ownerManager->update();
    }

}