<?php

namespace Biz\User\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface TokenDao extends GeneralDaoInterface
{
    public function get($id, $lock = false);

    public function getByToken($token);

    public function create($token);

    public function findByUserIdAndType($userId, $type);

    public function getByType($type);

    public function deleteTopsByExpiredTime($expiredTime, $limit);
}
