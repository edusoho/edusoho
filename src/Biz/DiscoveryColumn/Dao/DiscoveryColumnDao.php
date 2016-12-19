<?php

namespace Topxia\Service\DiscoveryColumn\Dao;

interface DiscoveryColumnDao
{
	public function getDiscoveryColumn($id);

	public function updateDiscoveryColumn($id, $fields);

	public function addDiscoveryColumn($CategoryShow);

	public function deleteDiscoveryColumn($id);

	public function findDiscoveryColumnByTitle($title);

	public function getAllDiscoveryColumns();
}