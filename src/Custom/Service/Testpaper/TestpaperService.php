<?php

namespace Custom\Service\Testpaper;

interface TestpaperService
{
    public function findUserTestpaperResultsByTestpaperIds(array $testpaperIds, $userId);
}
