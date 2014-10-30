<?php

namespace Topxia\Service\Classes\Dao;

interface ClassMemberDao
{
	public function getClassMember($id);

    public function getMemberByUserId($userId);

    public function getMemberByClassIdAndRole($classId, $role);

    public function getMemberByUserIdAndClassId($userId, $classId);

    public function getStudentMemberByUserIdAndClassId($userId, $classId);

    public function findStudentMembersByUserIdsAndClassId($userIds, $classId);

    public function findMembersByClassIdAndRole($classId, $role);

    public function findClassMembersByUserIds(array $userIds);
	
	public function searchClassMembers($conditions, $orderBy, $start, $limit);

	public function searchClassMemberCount($conditions);

	public function addClassMember($classMember);

	public function updateClassMember($fields, $id);

	public function deleteClassMemberByUserId($userId);
}