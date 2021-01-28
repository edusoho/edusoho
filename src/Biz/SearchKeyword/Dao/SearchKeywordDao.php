<?php

namespace Biz\SearchKeyword\Dao;

interface SearchKeywordDao
{
    public function getByNameAndType($name, $type);
}
