<?php

namespace Biz\Taxonomy\Event;

use Biz\Taxonomy\TagOwnerManager;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ArticleEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'article.create' => 'onArticleCreate',
            'article.update' => 'onArticleUpdate',
        );
    }

    public function onArticleCreate(Event $event)
    {
        $article = $event->getSubject();

        $tagIds = $event->getArgument('tagIds');
        $userId = $event->getArgument('userId');

        $tagOwnerManager = new TagOwnerManager('article', $article['id'], $tagIds, $userId);
        $tagOwnerManager->create();
    }

    public function onArticleUpdate(Event $event)
    {
        $article = $event->getSubject();
        $tagIds = $event->getArgument('tagIds');
        $userId = $event->getArgument('userId');

        $tagOwnerManager = new TagOwnerManager('article', $article['id'], $tagIds, $userId);
        $tagOwnerManager->update();
    }
}
