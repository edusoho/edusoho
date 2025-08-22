<?php

namespace Biz\QuestionTag\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface QuestionTagGroupDao extends AdvancedDaoInterface
{
    public function getByName($name);
}
