<?php
namespace TestProject\Biz\Example\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ExampleDao extends GeneralDaoInterface
{
    public function findByName($name, $start, $limit);

    public function findByNameAndId($name, $ids1);
}
