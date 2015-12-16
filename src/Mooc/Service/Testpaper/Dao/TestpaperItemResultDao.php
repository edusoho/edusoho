<?php

namespace Mooc\Service\Testpaper\Dao;

interface TestpaperItemResultDao
{
    public function searchTestpaperItemResultsCount($conditions);

    public function searchTestpaperItemResults($conditions, $sort, $start, $limit);
}
