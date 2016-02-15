<?php
namespace Topxia\Service\Article\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;

class ArticleEventSubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return array(
            'article.liked' => 'onArticleLike',
            'article.cancelLike' => 'onArticleCancelLike',
            'article.post_create' => 'onPostCreate',
            'article.post_delete' => 'onPostDelete',
        );
    }

    public function onArticleLike(ServiceEvent $event)
    {
        $article = $event->getSubject();
        $this->getArticleService()->count($article['id'], 'upsNum', +1);
    }

    public function onArticleCancelLike(ServiceEvent $event)
    {
        $article = $event->getSubject();
        $this->getArticleService()->count($article['id'], 'upsNum', -1);
    }

    public function onPostCreate(ServiceEvent $event)
    {
        $post = $event->getSubject();
        if ($post['parentId'] == 0) {
            $this->getArticleService()->count($post['targetId'], 'postNum', +1);
        }
    }

    public function onPostDelete(ServiceEvent $event)
    {
        $post = $event->getSubject();
        if ($post['parentId'] == 0) {
            $this->getArticleService()->count($post['targetId'], 'postNum', -1);
        }
    }

    protected function getArticleService()
    {
        return ServiceKernel::instance()->createService('Article.ArticleService');
    }
}
