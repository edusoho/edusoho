<?php

namespace Biz\Dictionary\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface DictionaryItemDao extends GeneralDaoInterface
{
    public function findAllOrderByWeight();

    public function findByName($name);

    public function findByType($type);
}
