<?php

namespace Mooc\Service\Testpaper\Dao;

interface TestpaperItemResultDao
{
    public function findTestpaperItemResultsByTestIdAndQuestionIdAndStatus($questionId, $testpaperId, $status);
}
