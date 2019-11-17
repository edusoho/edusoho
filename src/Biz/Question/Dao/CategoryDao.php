<?php

namespace Biz\Question\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface CategoryDao extends GeneralDaoInterface
{
    public function findByBankId($bankId);
}
