<?php

namespace Biz\Taxonomy\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface TagOwnerDao extends GeneralDaoInterface
{
    public function findByOwnerTypeAndOwnerId($ownerType, $ownerId);

    public function findByTagIdsAndOwnerType($tagIds, $ownerType);

    public function findByOwnerTypeAndOwnerIds($ownerType, $ownerIds);

    public function updateByOwnerTypeAndOwnerId($ownerType, $ownerId, $fields);

    public function deleteByOwnerTypeAndOwnerId($ownerType, $ownerId);

    public function getTagOwnerRelationByTagIdAndOwnerTypeAndOwnerId($tagId, $ownerType, $ownerId);
}
