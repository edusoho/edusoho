<?php

namespace SensitiveWord\Service\Sensitive\Dao;

interface KeywordBanlogDao
{
    public  function addBanlog(array $fields);

    public function getBanlog($id);

    public function searchBanlogsCount($conditions);

    public function searchBanlogs($conditions, $orderBy, $start, $limit);
}