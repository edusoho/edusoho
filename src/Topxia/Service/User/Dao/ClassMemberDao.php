<?php

namespace Topxia\Service\User\Dao;

interface ClassMemberDao
{
	public function getClassMember($id);
	
	public function searchClassMembers($conditions, $orderBy, $start, $limit);

	public function searchClassMemberCount($conditions);

	public function addClassMember($classMember);
}