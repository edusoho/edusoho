<?php

namespace Biz\Dictionary\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface DictionaryDao extends GeneralDaoInterface
{
    public function findAll();
}
