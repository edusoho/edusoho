<?php

namespace Biz\Review\Event;

use Biz\Review\Service\ReviewService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ReviewEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'review.delete' => 'onReviewDelete',
        ];
    }

    public function onReviewDelete(Event $event)
    {
        $review = $event->getSubject();

        if ($review['parentId'] > 0) {
            return;
        }

        $this->getReviewService()->deleteReviewsByParentId($review['id']);
    }

    /**
     * @return ReviewService
     */
    protected function getReviewService()
    {
        return $this->getBiz()->service('Review:ReviewService');
    }
}
