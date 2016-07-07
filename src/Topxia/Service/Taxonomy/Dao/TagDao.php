<?php
namespace Topxia\Service\Taxonomy\Dao;

interface TagDao
{
    public function addTag(array $tag);

    public function updateTag($id, array $fields);

    public function findTagsByIds(array $ids);

    public function findTagsByNames(array $names);

    public function findAllTags($start, $limit);

    public function searchTags($conditions, $start, $limit);

    public function searchTagCount($conditions);

    public function getTag($id);

    public function getTagByName($name);

    public function getTagByLikeName($name);

    public function findAllTagsCount();

    public function deleteTag($id);
}
