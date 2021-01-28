<?php

namespace Biz\WeChat\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface UserWeChatDao extends AdvancedDaoInterface
{
    public function findByUserId($userId);

    public function findByUserIdAndType($userId, $type);

    public function findOpenIdsInListsByType($openIds, $type);

    public function findAllBindUserIds();

    public function getByUserIdAndType($userId, $type);

    public function getByTypeAndUnionId($type, $unionId);

    public function getByTypeAndOpenId($type, $openId);

    public function countWeChatUserJoinUser($conditions);

    public function searchWeChatUsersJoinUser($conditions, $orderBys, $start, $limit);
}
