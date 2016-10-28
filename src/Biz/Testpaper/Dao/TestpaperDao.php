<?php

namespace Biz\Testpaper\Dao;

interface TestpaperDao
{
    public function findTestpapersByIds(array $ids);

    public function findTestpaperByTargets(array $targets);

    public function findTestpapersByCopyIdAndLockedTarget($copyId, $lockedTarget);
}
