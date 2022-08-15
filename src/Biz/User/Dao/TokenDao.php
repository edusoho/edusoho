<?php

namespace Biz\User\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface TokenDao extends GeneralDaoInterface
{
    public function get($id, array $options = array());

    public function getByToken($token);

    public function create($token);

    public function findByUserIdAndType($userId, $type);

    public function destroyTokensByUserId($userId);

    public function getByType($type);

    public function deleteTopsByExpiredTime($expiredTime, $limit);

    public function deleteByTypeAndUserId($type, $userId);
}
