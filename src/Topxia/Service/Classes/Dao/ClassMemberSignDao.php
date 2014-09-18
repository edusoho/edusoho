<?php

namespace Topxia\Service\Classes\Dao;

interface ClassMemberSignDao
{
	public function addClassMemberSign($ClassMemberSign);

	public function getClassMemberSign($id);

	public function findClassMemberSignByPeriod($userId, $classId, $startTime, $EndTime);
}