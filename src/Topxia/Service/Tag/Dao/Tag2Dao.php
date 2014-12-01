<?php
namespace Topxia\Service\Tag\Dao;

interface Tag2Dao
{
    public function getTag2($id);

    public function addTag2(array $tag);

    public function updateTag2($id, array $fields);

    public function updateTagToDisabled($id);

    public function updateTag2sByGroupId($groupId,$newGroupId);

    public function updateTagToDisabledByGroupId($groupId);

    public function findTagsByIds(array $ids);

    public function findAllTags();

    public function findTag2sByNames(array $names);

    public function findTagsByTagGroupIds(array $groupIds);

    public function getTag2ByName($name);

    public function getDisabledTag2ByName($name);

    public function getTag2ByLikeName($name);    
}