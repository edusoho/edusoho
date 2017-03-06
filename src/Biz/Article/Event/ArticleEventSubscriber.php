<?php

namespace Biz\Article\Event;

use Biz\Article\Service\ArticleService;
use Biz\Taxonomy\TagOwnerManager;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ArticleEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'article.liked' => 'onArticleLike',
            'article.delete' => 'onArticleDelete',
            'article.cancelLike' => 'onArticleCancelLike',
            'article.post_create' => 'onPostCreate',
            'article.post_delete' => 'onPostDelete',
        );
    }

    public function onArticleDelete(Event $event)
    {
        $article = $event->getSubject();

        $tagOwnerManager = new TagOwnerManager('article', $article['id']);
        $tagOwnerManager->delete();
    }

    public function onArticleLike(Event $event)
    {
        $article = $event->getSubject();
        $this->getArticleService()->count($article['id'], 'upsNum', +1);
    }

    public function onArticleCancelLike(Event $event)
    {
        $article = $event->getSubject();
        $this->getArticleService()->count($article['id'], 'upsNum', -1);
    }

    public function onPostCreate(Event $event)
    {
        $post = $event->getSubject();
        if ($post['parentId'] == 0) {
            $this->getArticleService()->count($post['targetId'], 'postNum', +1);
        }
    }

    public function onPostDelete(Event $event)
    {
        $post = $event->getSubject();
        if ($post['parentId'] == 0) {
            $this->getArticleService()->count($post['targetId'], 'postNum', -1);
        }
    }

    /**
     * @return ArticleService
     */
    protected function getArticleService()
    {
        return $this->getBiz()->service('Article:ArticleService');
    }
}
