<?php

namespace Biz\User\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface UserProfileDao extends GeneralDaoInterface
{
    public function findByIds(array $ids);

    public function dropFieldData($fieldName);

    public function findDistinctMobileProfiles($start, $limit);
}
