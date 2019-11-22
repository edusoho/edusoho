<?php

namespace Biz\QuestionBank\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface QuestionBankDao extends GeneralDaoInterface
{
    public function findAll();
}
