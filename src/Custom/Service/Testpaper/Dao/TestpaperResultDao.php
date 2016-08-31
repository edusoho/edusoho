<?php

namespace Custom\Service\Testpaper\Dao;

interface TestpaperResultDao
{
    public function findTestPaperResultCountByStatusAndTestIdsAndOrgId($ids, $status, $orgCode);

    public function findTestPaperResultsByStatusAndTestIdsAndOrgId($ids, $status, $orgCode);
}