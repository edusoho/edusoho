<?php

namespace Topxia\Service\Taxonomy\Dao;

interface CategoryGroupDao
{
    public function getGroup($id);

    public function findGroupByCode($code);

    public function findGroups($start, $limit);

    /**
    *分类的分组系统初始化时初始化好，此方法仅仅给单元测试使用
    */
    public function addGroup(array $group);
}