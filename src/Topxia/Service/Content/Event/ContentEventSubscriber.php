<?php
namespace Topxia\Service\Content\Event;

use Topxia\Service\Common\ServiceEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Topxia\Service\Taxonomy\TagOwnerManager;

class ContentEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'content.delete' => 'onContentDelete',
            'content.create' => 'onContentCreate',
            'content.update' => 'onContentUpdate'
        );
    }

    public function onContentDelete(ServiceEvent $event)
    {
        $contentId = $event->getSubject();

        $tagOwnerManager = new TagOwnerManager('content', $contentId);
        $tagOwnerManager->delete();
    }

    public function onContentCreate(ServiceEvent $event)
    {
        $fields = $event->getSubject();

        $contentId = $fields['contentId'];
        $tagIds    = $fields['tagIds'];
        $userId    = $fields['userId'];

        $tagOwnerManager = new TagOwnerManager('content', $contentId, $tagIds, $userId);
        $tagOwnerManager->create();
    }

    public function onContentUpdate(ServiceEvent $event)
    {
        $fields = $event->getSubject();

        $contentId = $fields['contentId'];
        $tagIds    = $fields['tagIds'];
        $userId    = $fields['userId'];

        $tagOwnerManager = new TagOwnerManager('content', $contentId, $tagIds, $userId);
        $tagOwnerManager->update();
    }
}
