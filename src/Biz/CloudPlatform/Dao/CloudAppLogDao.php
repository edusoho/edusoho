<?php

namespace Biz\CloudPlatform\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface CloudAppLogDao extends GeneralDaoInterface
{
    public function getLastLogByCodeAndToVersion($code, $toVersion);

    public function find($start, $limit);

    public function countLogs();
}
