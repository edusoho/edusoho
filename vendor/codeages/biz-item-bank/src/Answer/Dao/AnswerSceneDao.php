<?php

namespace Codeages\Biz\ItemBank\Answer\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface AnswerSceneDao extends AdvancedDaoInterface
{
    public function findNotStatisticsQuestionsReportScenes($limited = 100);
}
