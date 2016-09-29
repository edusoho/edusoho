<?php
namespace Custom\Service\Testpaper\Impl;

use Topxia\Service\Testpaper\Impl\TestpaperServiceImpl as BaseService;

class TestpaperServiceImpl extends BaseService
{
    public function findTestPaperResultCountByStatusAndTestIdsAndUserIds($ids, $status, $userIds)
    {
        return $this->getTestpaperResultDao()->findTestPaperResultCountByStatusAndTestIdsAndUserIds($ids, $status, $userIds);
    }

    public function findTestPaperResultsByStatusAndTestIdsAndUserIds($ids, $status, $userIds)
    {
        return $this->getTestpaperResultDao()->findTestPaperResultsByStatusAndTestIdsAndUserIds($ids, $status, $userIds);
    }
    
}