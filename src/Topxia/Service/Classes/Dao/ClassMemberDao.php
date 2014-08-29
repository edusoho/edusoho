<?php

namespace Topxia\Service\Classes\Dao;

interface ClassMemberDao
{
	public function getClassMember($id);
	
	public function searchClassMembers($conditions, $orderBy, $start, $limit);

	public function searchClassMemberCount($conditions);

	public function addClassMember($classMember);

	public function updateClassMember($fields, $id);

	public function deleteClassMemberByUserId($userId);
}