<?php

namespace Biz\User\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface UserSecureQuestionDao extends GeneralDaoInterface
{
    public function findByUserId($userId);
}
