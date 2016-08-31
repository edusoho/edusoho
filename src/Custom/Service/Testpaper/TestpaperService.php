<?php

namespace Custom\Service\Testpaper;

interface TestpaperService
{
    public function findTestPaperResultCountByStatusAndTestIdsAndOrgId($ids, $status, $orgId);

    public function findTestPaperResultsByStatusAndTestIdsAndOrgId($ids, $status, $orgId);

    public function findAllTestpapersByTarget($id);
}