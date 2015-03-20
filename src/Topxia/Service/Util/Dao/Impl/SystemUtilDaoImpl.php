<?php

namespace Topxia\Service\Util\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Util\Dao\SystemUtilDao;

class SystemUtilDaoImpl extends BaseDao implements SystemUtilDao
{
   public function getCourseIdsWhereCourseHasDeleted()
   {
        $sql = "SELECT DISTINCT  targetId FROM upload_files WHERE "; 
        $sql .= " targetType='courselesson' and targetId NOT IN (SELECT id FROM course)";
        return $this->getConnection()->fetchAll($sql);    
   }


}