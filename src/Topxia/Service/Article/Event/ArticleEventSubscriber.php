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

    private function getArticleService()
    {
        return ServiceKernel::instance()->createService('Article.ArticleService');
    }
}
