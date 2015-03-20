<?php

namespace Topxia\Service\Taxonomy\Dao;

interface CategoryGroupDao
{
    public function getGroup($id);

    public function findGroupByCode($code);

    public function findGroups($start, $limit);

    public function findAllGroups();

    public function addGroup(array $group);

    public function deleteGroup($id);
}