<?php

namespace Codeages\Biz\ItemBank\Item\Job;

use Codeages\Biz\Framework\Scheduler\AbstractJob;
use Codeages\Biz\ItemBank\Item\Service\QuestionFavoriteService;

class DeleteQuestionFavoriteJob extends AbstractJob
{
    public function execute()
    {
        $this->getQuestionFavoriteService()->deleteByItemIds($this->args['itemIds']);
    }

    /**
     * @return QuestionFavoriteService
     */
    private function getQuestionFavoriteService()
    {
        return $this->biz->service('ItemBank:Item:QuestionFavoriteService');
    }
}
