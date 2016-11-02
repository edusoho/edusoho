<?php

namespace Biz\Task\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface TaskResultDao extends GeneralDaoInterface
{
    public function findByCourseId($courseId);

    public function findByTaskId($courseTaskId);

    public function save($taskResult);
}
