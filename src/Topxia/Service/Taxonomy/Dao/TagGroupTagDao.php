<?php
namespace Topxia\Service\Taxonomy\Dao;

interface TagGroupTagDao
{
    public function getTagGroupByGroupId($groupId);

    public function create($fields);

    public function findTagsByGroupId($groupId);

    public function update($groupId, $fields);

    public function delete($groupId);
}
