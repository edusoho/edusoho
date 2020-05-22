<?php

namespace Codeages\Biz\ItemBank\Assessment\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface AssessmentDao extends GeneralDaoInterface
{
    public function findByIds($ids);
}
