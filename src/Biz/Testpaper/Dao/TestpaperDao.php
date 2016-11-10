<?php

namespace Biz\Testpaper\Dao;

interface TestpaperDao
{
    public function findTestpapersByIds(array $ids);

    public function findTestpapersByCopyIdAndLockedTarget($copyId, $lockedTarget);
}
