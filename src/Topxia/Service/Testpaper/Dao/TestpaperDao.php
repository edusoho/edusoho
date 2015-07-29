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
   
    public function updateTestpaperByPid($pId,$fields);

    public function deleteTestpaper($id);

    public function deleteTestpaperByPid($pId);

    public function findTestpaperByTargets(array $targets);

    public function findTestpaperIdsByPid($pId);
}