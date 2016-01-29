<?php

namespace SensitiveWord\Service\Sensitive\Dao;

interface SensitiveDao
{
    public function getKeywordByName($name);

    public function findAllKeywords();

    public function addKeyword(array $fields);

    public function deleteKeyword($id);

    public function updateKeyword($id, $conditions);

    public function searchkeywordsCount($conditions);

    public function searchKeywords($condtions, $orderBy, $start, $limit);

    public function waveBannedNum($id, $diff);

    public function findKeywordsByState($state);

}
