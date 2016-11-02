<?php

namespace Biz\Task\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface TaskResultDao extends GeneralDaoInterface
{
    public function findByCourseId($courseId, $userId);

    public function findByTaskId($courseTaskId, $userId);

    public function save($taskResult);
}
