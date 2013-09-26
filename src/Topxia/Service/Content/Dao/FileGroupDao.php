<?php

namespace Topxia\Service\Content\Dao;

interface FileGroupDao
{

	public function getGroup($id);

	public function findGroupByCode($code);

	public function findAllGroups();

	public function addGroup($group);

	public function deleteGroup($id);

}