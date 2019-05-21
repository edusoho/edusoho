<?php

namespace Biz\WeChat\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface UserWeChatDao extends AdvancedDaoInterface
{
    public function findByUserId($userId);

    public function findByUserIdAndType($userId, $type);

    public function findOpenIdsInListsByType($openIds, $type);
}
