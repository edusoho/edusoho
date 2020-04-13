<?php

namespace  Biz\Activity\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ExerciseActivityDao extends GeneralDaoInterface
{
    public function findByIds($ids);

    public function getByAnswerSceneId($answerSceneId);
}
