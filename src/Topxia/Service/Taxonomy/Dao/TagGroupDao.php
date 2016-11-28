<?php
namespace Topxia\Service\Taxonomy\Dao;

interface TagGroupDao
{
    public function get($id);

    public function findTagGroupByName($name);

    public function findTagGroups();

    public function findTagGroupsByGroupIds($groupIds);

    public function create($fields);

    public function delete($id);

    public function update($id, $fields);
}
