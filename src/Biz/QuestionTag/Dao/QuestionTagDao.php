<?php

namespace Biz\QuestionTag\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface QuestionTagDao extends AdvancedDaoInterface
{
    public function getByGroupIdAndName($groupId, $name);
}
