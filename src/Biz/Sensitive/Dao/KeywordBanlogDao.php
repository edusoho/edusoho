<?php

namespace Biz\Sensitive\Dao;

interface KeywordBanlogDao
{
    public function searchBanlogsByUserIds($userIds, $orderBy, $start, $limit);
}
