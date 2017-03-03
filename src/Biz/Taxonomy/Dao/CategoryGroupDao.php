<?php

namespace Biz\Taxonomy\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface CategoryGroupDao extends GeneralDaoInterface
{
    public function getByCode($code);

    public function find($start, $limit);

    public function findAll();
}
