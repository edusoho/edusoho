<?php

namespace Topxia\Service\Quiz\Dao;

interface TestItemResultDao
{
    public function getResult($id);

    public function addResult($questions);

    public function updateResult($id, $fields);

    public function deleteResult($id);

    public function deleteResultsByParentId($id);

    public function findResultByIds(array $ids);

    public function deleteResultByIds(array $ids);

    public function searchResultCount($conditions);

    public function searchResult($conditions, $orderBy, $start, $limit);
}