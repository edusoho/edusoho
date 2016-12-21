<?php

namespace Biz\User\Dao;

interface UserProfileDao
{
    public function findByIds(array $ids);

    public function dropFieldData($fieldName);

    public function findDistinctMobileProfiles($start, $limit);
}
