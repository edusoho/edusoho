<?php

namespace Biz\Course\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ThreadDao extends GeneralDaoInterface
{
    public function deleteByCourseId($courseId);
}
