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
     * @Log(module="tag",action="create")
     */
    public function addTag(array $tag);

    /**
     * @param $fields
     *
     * @return mixed
     * @Log(module="tagGroup",action="create")
     */
    public function addTagGroup($fields);

    /**
     * @param $id
     * @param array $fields
     *
     * @return mixed
     * @Log(module="tag",action="update",param="id")
     */
    public function updateTag($id, array $fields);

    /**
     * @param $id
     * @param $fields
     *
     * @return mixed
     * @Log(module="tagGroup",action="update",param="id")
     */
    public function updateTagGroup($id, $fields);

    /**
     * @param $id
     *
     * @return mixed
     * @Log(module="tag",action="delete")
     */
    public function deleteTag($id);

    /**
     * @param $id
     *
     * @return mixed
     * @Log(module="tagGroup",action="delete")
     */
    public function deleteTagGroup($id);

    public function findTagIdsByOwnerTypeAndOwnerIds($ownerType, array $ids);

    public function findOwnerIdsByTagIdsAndOwnerType($tagIds, $ownerType);

    public function findGroupTagIdsByOwnerTypeAndOwnerIds($ownerType, array $ids);
}
