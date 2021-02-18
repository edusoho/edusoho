<?php

namespace Codeages\Biz\ItemBank\Item\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface QuestionDao extends AdvancedDaoInterface
{
    public function findByItemId($itemId);

    public function findByItemsIds($itemIds);

    public function findQuestionsByQuestionIds($questionIds);
}
