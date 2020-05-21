<?php

namespace Codeages\Biz\ItemBank\Item\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface ItemCategoryDao extends AdvancedDaoInterface
{
    public function findByIds($ids);

    public function findByBankId($bankId);
}
