<?php
namespace Topxia\Service\Taxonomy\Dao;

interface TagOwnerDao
{
    public function get($id);

    public function findByOwnerTypeAndOwnerId($ownerType, $ownerId);

    public function findByOwnerTypeAndOwnerIds($ownerType, $ownerIds);

    public function findByTagIdsAndOwnerType($tagIds, $ownerType);

    public function add($fields);

    public function updateByOwnerTypeAndOwnerId($ownerType, $ownerId, $fields);

    public function deleteByOwnerTypeAndOwnerId($ownerType, $ownerId);
}
