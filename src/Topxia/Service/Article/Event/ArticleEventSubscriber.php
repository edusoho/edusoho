<?php
namespace Topxia\Service\Article\Event;

use Codeages\Biz\Framework\Event\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\Taxonomy\TagOwnerManager;

class ArticleEventSubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return array(
            'article.liked'       => 'onArticleLike',
            'article.delete'      => 'onArticleDelete',
            'article.cancelLike'  => 'onArticleCancelLike',
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

    protected function getArticleService()
    {
        return ServiceKernel::instance()->createService('Article.ArticleService');
    }
}
