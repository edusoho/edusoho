<?php

namespace Biz\Course\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

class CourseSetDao extends GeneralDaoInterface
{
	public function findByCourseId($courseId);
}
