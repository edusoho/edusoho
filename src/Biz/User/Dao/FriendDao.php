<?php

namespace Biz\User\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface FriendDao extends GeneralDaoInterface
{
    public function updateByFromIdAndToId($fromId, $toId, $fields);

    public function getByFromIdAndToId($fromId, $toId);

    public function findByFromIdAndToIds($fromId, array $toIds);

    public function searchByFromId($fromId, $start, $limit);

    public function findFollowingsByFromId($fromId);

    public function findFollowersByToId($toId);

    public function searchByToId($toId, $start, $limit);

    public function searchByUserId($userId, $start, $limit);
}
