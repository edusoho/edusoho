<?php

namespace Mooc\Service\Testpaper;

interface TestpaperService
{
    public function findUserTestpaperResultsByTestpaperIds(array $testpaperIds, $userId);

    public function searchTestpaperItemResultsCount($conditions);

    public function searchTestpaperItemResults($conditions, $orderBy, $start, $limit);
}
