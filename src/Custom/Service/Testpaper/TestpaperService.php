<?php

namespace Custom\Service\Testpaper;

interface TestpaperService
{
    public function findTestPaperResultCountByStatusAndTestIdsAndUserIds($ids, $status, $userIds);

    public function findTestPaperResultsByStatusAndTestIdsAndUserIds($ids, $status, $userIds);

    public function findAllTestpapersByTarget($id);
}