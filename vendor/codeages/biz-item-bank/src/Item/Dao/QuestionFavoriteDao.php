<?php

namespace Codeages\Biz\ItemBank\Item\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface QuestionFavoriteDao extends AdvancedDaoInterface
{
    public function deleteByQuestionFavorite($questionFavorite);
}
