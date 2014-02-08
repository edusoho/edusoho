<?php

namespace Topxia\Service\Testpaper\Dao;

interface TestpaperResultDao
{
    public function getTestpaperResult($id);

    public function findTestpaperResultsByIds(array $ids);

    public function searchTestpaperResults($conditions, $sort, $start, $limit);

    public function searchTestpaperResultsCount($conditions);

    public function addTestpaperResult($fields);

    public function updateTestpaperResult($id, $fields);
}