<?php

namespace Mooc\Service\Testpaper\Impl;

use Topxia\Service\Testpaper\Impl\TestpaperServiceImpl as BaseTestpaperServiceImpl;

class TestpaperServiceImpl extends BaseTestpaperServiceImpl
{
    public function findUserTestpaperResultsByTestpaperIds(array $testpaperIds, $userId)
    {
        return $this->getTestpaperResultDao()->findUserTestpaperResultsByTestpaperIds($testpaperIds, $userId);
    }
}
