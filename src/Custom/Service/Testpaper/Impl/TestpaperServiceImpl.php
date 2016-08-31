<?php
namespace Custom\Service\Testpaper\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\BaseService;
use Custom\Service\Testpaper\TestpaperService;

class TestpaperServiceImpl extends BaseService implements TestpaperService
{
    public function findTestpaperResultCountByStatusAndTestIdsAndOrgId($testpaperIds, $status, $orgCode)
    {
        return $this->getTestpaperResultDao()->findTestpaperResultCountByStatusAndTestIdsAndOrgId($testpaperIds, $status, $orgCode);
    }

    public function findTestpaperResultsByStatusAndTestIdsAndOrgId($testpaperIds, $status, $orgCode)
    {
        return $this->getTestpaperResultDao()->findTestpaperResultsByStatusAndTestIdsAndOrgId($testpaperIds, $status, $orgCode);
    }

    public function findAllTestpapersByTarget($id)
    {
        $target = 'course-'.$id;
        return $this->getTestpaperDao()->findTestpaperByTargets(array($target));
    }

    protected function getTestpaperResultDao()
    {
        return $this->createDao('Custom:Testpaper.TestpaperResultDao');
    }

    protected function getTestpaperDao()
    {
        return $this->createDao('Testpaper.TestpaperDao');
    }
}