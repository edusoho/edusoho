<?php
namespace Topxia\Service\Taxonomy;

interface TagService
{   
    //tag_owner
    public function addTagOwnerRelation($fields);
    
    public function findTagsByOwner(array $owner);

    public function findTagOwnerRelationsByTagIdsAndOwnerType($tagIds, $ownerType);

    public function getTagOwnerRelationByTagIdAndOwner($tagId, $owner);

    //tagIds
    public function findTagIdsByOwnerTypeAndOwnerIds($ownerType, array $ids);

    public function deleteTagOwnerRelationsByOwner(array $owner);

    public function getTag($id);

    public function getTagGroup($id);

    public function getTagByName($name);

    public function getTagByLikeName($name);

    public function findAllTags($start, $limit);

    public function findTagGroups();

    public function findTagsByGroupId($groupId);

    public function findTagRelationsByTagIds($tagIds);

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
