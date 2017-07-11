<?php

namespace Tests\Example\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ExampleDao extends GeneralDaoInterface
{
    public function findByName($name, $start, $limit);

    public function findByNameAndId($name, $ids1);

    public function findByIds(array $ids, array $orderBys, $start, $limit);

    public function updateByNameAndCode($name, $code, array $fields);
}
