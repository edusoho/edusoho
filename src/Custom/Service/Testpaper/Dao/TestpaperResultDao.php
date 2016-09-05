<?php

namespace Custom\Service\Testpaper\Dao;

interface TestpaperResultDao
{
    public function findTestPaperResultCountByStatusAndTestIdsAndUserIds($ids, $status, $userIds);

    public function findTestPaperResultsByStatusAndTestIdsAndUserIds($ids, $status, $userIds);
}