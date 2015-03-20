<?php

namespace Topxia\Service\Testpaper\Dao;

interface TestpaperDao
{
    public function getTestpaper($id);

    public function findTestpapersByIds(array $ids);

    public function searchTestpapers($conditions, $sort, $start, $limit);

    public function searchTestpapersCount($conditions);

    public function addTestpaper($fields);

    public function updateTestpaper($id, $fields);

    public function deleteTestpaper($id);

    public function findTestpaperByTargets(array $targets);
}