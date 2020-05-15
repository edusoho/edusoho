<?php

namespace Codeages\Biz\ItemBank\Item\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface ItemDao extends AdvancedDaoInterface
{
    public function findByIds($ids);

    public function findByCategoryIds($categoryIds);

    public function getItemCountGroupByTypes($conditions);
}
