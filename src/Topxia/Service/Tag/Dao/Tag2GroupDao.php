<?php
namespace Topxia\Service\Tag\Dao;

interface Tag2GroupDao
{
    public function getTag2Group($id);

    public function addTag2Group(array $tag);

    public function updateTag2Group($id, array $fields);

    public function updateTagGroupToDisabled($id);

    public function findTag2GroupsByIds(array $ids);

    public function findTag2GroupsByNames(array $names);

    public function findTag2GroupsByTypes(array $types);

    public function findAllTagGroups();

    public function findAllTag2Groups($start, $limit);

    public function getTag2GroupByName($name);

    public function getDisabledTag2GroupByName($name);

    public function getTag2GroupByLikeName($name);

    public function findAllTag2GroupsCount();
}