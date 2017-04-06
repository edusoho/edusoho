<?php

namespace Biz\Org\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface OrgDao extends GeneralDaoInterface
{
    public function findByIds($ids);

    public function getByCode($value);

    public function deleteByPrefixOrgCode($orgCode);

    public function findByPrefixOrgCode($orgCode);

    public function getByNameAndParentId($name, $parentId);
}
