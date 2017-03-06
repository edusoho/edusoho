<?php

namespace Biz\Content\Event;

use Biz\Taxonomy\TagOwnerManager;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ContentEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'content.delete' => 'onContentDelete',
            'content.create' => 'onContentCreate',
            'content.update' => 'onContentUpdate',
        );
    }

    public function onContentDelete(Event $event)
    {
        $contentId = $event->getSubject();

        $tagOwnerManager = new TagOwnerManager('content', $contentId);
        $tagOwnerManager->delete();
    }

    public function onContentCreate(Event $event)
    {
        $fields = $event->getSubject();

        $contentId = $fields['contentId'];
        $tagIds = $fields['tagIds'];
        $userId = $fields['userId'];

        $tagOwnerManager = new TagOwnerManager('content', $contentId, $tagIds, $userId);
        $tagOwnerManager->create();
    }

    public function onContentUpdate(Event $event)
    {
        $fields = $event->getSubject();

        $contentId = $fields['contentId'];
        $tagIds = $fields['tagIds'];
        $userId = $fields['userId'];

        $tagOwnerManager = new TagOwnerManager('content', $contentId, $tagIds, $userId);
        $tagOwnerManager->update();
    }
}
