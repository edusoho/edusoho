<?php

namespace Mooc\Service\Testpaper\Dao;

interface TestpaperResultDao
{
    public function findUserTestpaperResultsByTestpaperIds(array $testpaperIds, $userId);
}
