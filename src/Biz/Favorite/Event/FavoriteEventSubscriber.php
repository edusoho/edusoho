<?php

namespace Biz\Favorite\Event;

use Biz\Favorite\Dao\FavoriteDao;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Event\EventSubscriber;

class FavoriteEventSubscriber extends EventSubscriber
{
    public static function getSubscribedEvents()
    {
        return [
            'group.thread.delete' => 'onGroupThreadDelete',
        ];
    }

    public function onGroupThreadDelete(Event $event)
    {
        $thread = $event->getSubject();
        $this->getFavoriteDao()->deleteByTargetTypeAndsTargetId('thread', $thread['id']);
    }

    /**
     * @return FavoriteDao
     */
    protected function getFavoriteDao()
    {
        return $this->getBiz()->dao('Favorite:FavoriteDao');
    }
}
