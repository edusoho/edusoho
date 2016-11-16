<?php
namespace Topxia\Service\Taxonomy\Dao;

interface TagOwnerDao
{
    public function getTagOwnerRelation($id);

    public function findTagOwnerRelationsByOwner(array $owner);

    public function findTagOwnerRelationsByTagIdsAndOwnerType($tagIds, $ownerType);

    public function addTagOwnerRelation($fields);

    public function updateTagOwnerRelationByOwner(array $owner, $fields);

    public function deleteTagOwnerRelationByOwner(array $owner);
}
