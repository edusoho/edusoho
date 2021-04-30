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
        return [
            'article.liked' => 'onArticleLike',
            'article.delete' => 'onArticleDelete',
            'article.cancelLike' => 'onArticleCancelLike',
            'article.post_create' => 'onPostCreate',
            'article.post_delete' => 'onPostDelete',
        ];
    }

    public function onArticleDelete(Event $event)
    {
        $article = $event->getSubject();

        // todo: 资讯删除,同时删除资讯评价
        $threadPosts = $this->getThreadService()->searchPosts(['targetType' => 'article', 'targetId' => $article['id'], 'parentId' => 0], [], 0, PHP_INT_MAX);
        foreach ($threadPosts as $threadPost) {
            $this->getThreadService()->deletePost($threadPost['id']);
        }

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
        if (0 == $post['parentId']) {
            $this->getArticleService()->count($post['targetId'], 'postNum', +1);
        }
    }

    public function onPostDelete(Event $event)
    {
        $post = $event->getSubject();
        if (0 == $post['parentId']) {
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

    /**
     * @return ThreadServiceImpl
     */
    protected function getThreadService()
    {
        return $this->getBiz()->service('Thread:ThreadService');
    }
}
