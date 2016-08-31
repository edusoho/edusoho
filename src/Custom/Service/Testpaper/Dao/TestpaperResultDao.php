<?php

namespace Custom\Service\Testpaper\Dao;

interface TestpaperResultDao
{
    public function findTestPaperResultCountByStatusAndTestIdsAndOrgId($ids, $status, $orgId);

    public function findTestPaperResultsByStatusAndTestIdsAndOrgId($ids, $status, $orgId);
}