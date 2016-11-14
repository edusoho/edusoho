<?php
namespace Topxia\Service\Taxonomy;

interface TagService
{
    public function getTag($id);

    public function getTagGroup($id);

    public function getTagByName($name);

    public function getTagByLikeName($name);

    public function findAllTags($start, $limit);

    public function findTagGroups();

    public function findTagsByGroupId($groupId);

    public function findTagRelationsByTagIds($tagIds);

    public function findTagRelationsByTagId($tagId);

    public function findTagGroupsByTagId($tagId);

    public function getAllTagCount();

    public function searchTags($conditions, $start, $limit);

    public function searchTagCount($conditions);

    public function findTagsByIds(array $ids);

    public function findTagsByNames(array $names);

    public function isTagNameAvalieable($name, $exclude = null);

    public function isTagGroupNameAvalieable($name, $exclude = null);

    public function addTag(array $tag);

    public function addTagGroup($fields);

    public function updateTag($id, array $fields);

    public function updateTagGroup($id, $fields);

    public function deleteTag($id);

    public function deleteTagGroup($id);
}
