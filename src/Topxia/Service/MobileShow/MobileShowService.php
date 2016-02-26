<?php

namespace Topxia\Service\MobileShow;

interface MobileShowService
{
	public function getMobileShow($id);

	public function updateMobileShow($id, $fields);

	public function addMobileShow($fields);

	public function deleteMobileShow($id);
	
	public function findMobileShowByTitle($title);

	public function getAllMobileShows();
}