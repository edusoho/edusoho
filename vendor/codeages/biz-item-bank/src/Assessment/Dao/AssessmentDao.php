<?php

namespace Codeages\Biz\ItemBank\Assessment\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface AssessmentDao extends AdvancedDaoInterface
{
    public function findByIds($ids);
    
    public function findTypes();
}
