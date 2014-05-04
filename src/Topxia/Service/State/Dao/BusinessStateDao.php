<?php

namespace Topxia\Service\State\Dao;

interface BusinessStateDao
{
	public function getBusinessState($id);

	public function findBusinessStatesByIds(array $ids);

    public function searchBusinessStates($conditions, $orderBy, $start, $limit);

    public function searchBusinessStateCount($conditions);

    public function addBusinessState($businessState);

	public function updateBusinessState($id, $fields);

	

}