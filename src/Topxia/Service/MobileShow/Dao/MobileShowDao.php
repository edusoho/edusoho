<?php

namespace Topxia\Service\MobileShow\Dao;

interface MobileShowDao
{
	public function getMobileShow($id);

	public function updateMobileShow($id, $fields);

	public function addMobileShow($CategoryShow);

	public function deleteMobileShow($id);

	public function findMobileShowByTitle($title);

	public function getAllMobileShows();
}