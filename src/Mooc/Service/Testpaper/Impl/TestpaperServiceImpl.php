<?php

namespace Mooc\Service\Testpaper\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Testpaper\Impl\TestpaperServiceImpl as BaseTestpaperServiceImpl;

class TestpaperServiceImpl extends BaseTestpaperServiceImpl
{
    public function findUserTestpaperResultsByTestpaperIds(array $testpaperIds, $userId)
    {
        return $this->getTestpaperResultDao()->findUserTestpaperResultsByTestpaperIds($testpaperIds, $userId);
    }

    public function searchTestpaperItemResultsCount($conditions)
    {
        return $this->getTestpaperItemResultDao()->searchTestpaperItemResultsCount($conditions);
    }

    public function searchTestpaperItemResults($conditions, $orderBy, $start, $limit)
    {
        return $this->getTestpaperItemResultDao()->searchTestpaperItemResults($conditions, $orderBy, $start, $limit);
    }

    public function isExistsEssay($itemResults)
    {
        $questions = $this->getQuestionService()->findQuestionsByIds(ArrayToolkit::column($itemResults, 'questionId'));

        foreach ($questions as $value) {
            if ($value['type'] == 'essay' || $value['type'] == 'fill') {
                return true;
            }
        }

        return false;
    }
}
