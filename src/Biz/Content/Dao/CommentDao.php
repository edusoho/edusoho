<?php

namespace Biz\Content\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface CommentDao extends GeneralDaoInterface
{
    public function findByObjectTypeAndObjectId($objectType, $objectId, $start, $limit);

    public function findByObjectType($objectType, $start, $limit);

    public function countByObjectType($objectType);
}
