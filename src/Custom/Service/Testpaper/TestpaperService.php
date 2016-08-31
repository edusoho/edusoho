<?php

namespace Custom\Service\Testpaper;

interface TestpaperService
{
    public function findTestPaperResultCountByStatusAndTestIdsAndOrgId($ids, $status, $orgCode);

    public function findTestPaperResultsByStatusAndTestIdsAndOrgId($ids, $status, $orgCode);

    public function findAllTestpapersByTarget($id);
}