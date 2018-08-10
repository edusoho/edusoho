<?php

namespace Biz\Taxonomy\Service;

use Biz\System\Annotation\Log;

interface TagService
{
    //tag_owner
    public function addTagOwnerRelation($fields);

    public function batchCreateTagOwner($tagOwners);

    public function findTagsByOwner(array $owner);

    public function findTagOwnerRelationsByTagIdsAndOwnerType($tagIds, $ownerType);

    public function getTagOwnerRelationByTagIdAndOwner($tagId, $owner);

    public function deleteTagOwnerRelationsByOwner(array $owner);

    public function getTag($id);

    public function getTagGroup($id);

    public function getTagByName($name);

    public function findTagsByLikeName($name);

    public function findAllTags($start, $limit);

    public function findTagGroups();

    public function findTagsByGroupId($groupId);

    public function findTagRelationsByTagIds($tagIds);

    public function findTagGroupsByTagId($tagId);

    public function getAllTagCount();

    public function searchTags($conditions, $sort, $start, $limit);

    public function searchTagCount($conditions);

    public function findTagsByIds(array $ids);

    public function findTagsByNames(array $names);

    public function isTagNameAvailable($name, $exclude = null);

    public function isTagGroupNameAvailable($name, $exclude = null);

    /**
     * @param $tag
     *
     * @return mixed
     * @Log(level="info",module="tag",action="create",message="添加标签",targetType="tag",param="result")
     */
    public function addTag(array $tag);

    /**
     * @param $fields
     *
     * @return mixed
     * @Log(level="info",module="tagGroup",action="create",message="添加标签组",targetType="tag_group",param="result")
     */
    public function addTagGroup($fields);

    public function updateTag($id, array $fields);

    public function updateTagGroup($id, $fields);

    public function deleteTag($id);

    public function deleteTagGroup($id);

    public function findTagIdsByOwnerTypeAndOwnerIds($ownerType, array $ids);

    public function findOwnerIdsByTagIdsAndOwnerType($tagIds, $ownerType);

    public function findGroupTagIdsByOwnerTypeAndOwnerIds($ownerType, array $ids);
}
