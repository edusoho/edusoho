<?php
namespace Custom\Service\Testpaper\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\BaseService;
use Custom\Service\Testpaper\TestpaperService;

class TestpaperServiceImpl extends BaseService implements TestpaperService
{
    public function findTestPaperResultCountByStatusAndTestIdsAndUserIds($ids, $status, $userIds)
    {
        return $this->getTestpaperResultDao()->findTestPaperResultCountByStatusAndTestIdsAndUserIds($ids, $status, $userIds);
    }

    public function findTestPaperResultsByStatusAndTestIdsAndUserIds($ids, $status, $userIds)
    {
        return $this->getTestpaperResultDao()->findTestPaperResultsByStatusAndTestIdsAndUserIds($ids, $status, $userIds);
    }

    public function findAllTestpapersByTarget($id)
    {
        $target = 'course-'.$id;
        return $this->getTestpaperDao()->findTestpaperByTargets(array($target));
    }

    public function findAllTestpapersByTargets(array $ids)
    {
        $targets = array();

        foreach ($ids as $id) {
            $targets[] = 'course-'.$id;
        }

        return $this->getTestpaperDao()->findTestpaperByTargets($targets);
    }

    public function findTestpapersByIds($ids)
    {
        $testpapers = $this->getTestpaperDao()->findTestpapersByIds($ids);
        return ArrayToolkit::index($testpapers, 'id');
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