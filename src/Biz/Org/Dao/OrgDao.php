<?php

namespace Biz\Org\Dao;

interface OrgDao
{
    public function findByIds($ids);

    public function getByCode($value);

    public function deleteByPrefixOrgCode($orgCode);

    public function findByPrefixOrgCode($orgCode);

    public function findByNameAndParentId($name, $parentId);

}
