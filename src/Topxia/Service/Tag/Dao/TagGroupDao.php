<?php
namespace Topxia\Service\Tag\Dao;

interface TagGroupDao
{
    public function getTagGroup($id);

    public function addTagGroup(array $tag);

    public function updateTagGroup($id, array $fields);

    public function updateTagGroupToDisabled($id);

    public function findTagGroupsByIds(array $ids);

    public function findTagGroupsByNames(array $names);

    public function findTagGroupsByTypes(array $types);

    public function findAllTagGroups();

    public function findAllTagGroupsByCount($start, $limit);

    public function getTagGroupByName($name);

    public function getDisabledTagGroupByName($name);

    public function getTagGroupByLikeName($name);

    public function findAllTagGroupsCount();
}