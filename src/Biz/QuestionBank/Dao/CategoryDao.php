<?php

namespace Biz\QuestionBank\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface CategoryDao extends AdvancedDaoInterface
{
    public function findByIds($ids);

    public function findAll();

    public function findAllByParentId($parentId);

    public function findByPrefixOrgCode($orgCode);
}
