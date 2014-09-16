<?php

namespace Topxia\Service\Classes\Dao;

interface ClassSignRelatedDao
{
	public function addClassSignRelated($classSignRelated);

	public function getClassSignRelated($id);

	public function updateClassSignRelated($classId, $fields);

	public function getUserClassSignRelated($classId);

}