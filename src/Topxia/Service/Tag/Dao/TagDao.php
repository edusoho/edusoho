<?php
namespace Topxia\Service\Tag\Dao;

interface TagDao
{
    public function getTag($id);

    public function addTag(array $tag);

    public function updateTag($id, array $fields);

    public function updateTagToDisabled($id);

    public function updateTagsByGroupId($groupId,$newGroupId);

    public function updateTagToDisabledByGroupId($groupId);

    public function findTagsByIds(array $ids);

    public function findAllTags();

    public function findTagsByNames(array $names);

    public function findTagsByTagGroupIds(array $groupIds);

    public function getTagByName($name);

    public function getDisabledTagByName($name);

    public function getTagByLikeName($name);    
}