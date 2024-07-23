<?php

namespace Codeages\Biz\ItemBank\Item\Event;

use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Event\EventSubscriber;
use Codeages\Biz\ItemBank\Item\Job\DeleteQuestionFavoriteJob;

class QuestionFavoriteEventSubscriber extends EventSubscriber
{
    public static function getSubscribedEvents()
    {
        return [
            'item.delete' => 'onItemDelete',
            'item.batchDelete' => 'onItemBatchDelete',
        ];
    }

    public function onItemDelete(Event $event)
    {
        $item = $event->getSubject();
        $this->deleteQuestionFavorite([$item['id']]);
    }

    public function onItemBatchDelete(Event $event)
    {
        $deleteItems = $event->getSubject();
        $this->deleteQuestionFavorite(array_column($deleteItems, 'id'));
    }

    private function deleteQuestionFavorite($itemIds)
    {
        $this->getSchedulerService()->register([
            'name' => 'DeleteQuestionFavoriteJob',
            'source' => 'MAIN',
            'expression' => intval(time()),
            'misfire_policy' => 'executing',
            'class' => DeleteQuestionFavoriteJob::class,
            'args' => ['itemIds' => $itemIds],
        ]);
    }

    /**
     * @return SchedulerService
     */
    private function getSchedulerService()
    {
        return $this->getBiz()->service('Scheduler:SchedulerService');
    }
}
