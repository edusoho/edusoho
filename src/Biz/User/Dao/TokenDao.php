<?php

namespace Biz\User\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface TokenDao extends AdvancedDaoInterface
{
    public function get($id, array $options = []);

    public function getByToken($token);

    public function findByTokens(array $tokens);

    public function findByUserIdAndType($userId, $type);

    public function destroyTokensByUserId($userId);

    public function getByType($type);

    public function deleteTopsByExpiredTime($expiredTime, $limit);

    public function deleteByTypeAndUserId($type, $userId);
}
