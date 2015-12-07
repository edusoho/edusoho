<?php

namespace Mooc\Service\Testpaper;

interface TestpaperService
{
    public function findUserTestpaperResultsByTestpaperIds(array $testpaperIds, $userId);

    public function findTestpaperItemResultsByTestIdAndQuestionIdAndStatus($testpaperId, $questionId, $status = true);
}
