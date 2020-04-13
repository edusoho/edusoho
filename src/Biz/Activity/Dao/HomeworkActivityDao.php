<?php

namespace Biz\Activity\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface HomeworkActivityDao extends GeneralDaoInterface
{
    public function getByAnswerSceneId($answerSceneId);

    public function findByIds($ids);
}
