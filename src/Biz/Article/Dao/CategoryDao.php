<?php

namespace Biz\Article\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface CategoryDao extends GeneralDaoInterface
{
    public function findByCode($code);

    public function getByParentId($parentId);

    public function findByParentId($parentId);

    public function findAll();

    public function searchByParentId($parentId, $orderBy, $start, $limit);

    public function countByParentId($parentId);

    public function findByIds(array $ids);

    public function findAllPublishedByParentId($parentId);
}
