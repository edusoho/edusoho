<?php

namespace Biz\Taxonomy\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface TagGroupDao extends GeneralDaoInterface
{
    public function getByName($name);

    public function find();

    public function findByIds($ids);
}
