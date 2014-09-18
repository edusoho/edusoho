<?php

namespace Topxia\Service\User\Dao;

interface UserRelationDao
{
	public function getUserRelation($id);

	public function addUserRelation($userRelation);

	public function findUserRelationsByFromIdAndType($fromId,$type);

}