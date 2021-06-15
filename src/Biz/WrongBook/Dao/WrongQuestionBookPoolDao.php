<?php

namespace Biz\WrongBook\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface WrongQuestionBookPoolDao extends AdvancedDaoInterface
{
    public function getPool($user_id, $target_type, $target_id);
}
