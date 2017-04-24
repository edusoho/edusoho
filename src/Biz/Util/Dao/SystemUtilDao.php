<?php

namespace Biz\Util\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface SystemUtilDao extends GeneralDaoInterface
{
    public function getCourseIdsWhereCourseHasDeleted();
}
