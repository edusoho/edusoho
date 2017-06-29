<?php

namespace Biz\Content\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface NavigationDao extends GeneralDaoInterface
{
    public function deleteByParentId($parentId);

    public function countAll();

    public function countByType($type);

    public function find($start, $limit);

    public function findByType($type, $start, $limit);
}
