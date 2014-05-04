<?php

namespace Topxia\Service\State\Dao;

interface PartnerStateDao
{
	public function getPartnerState($id);

	public function findPartnerStatesByIds(array $ids);

    public function searchPartnerStates($conditions, $orderBy, $start, $limit);

    public function searchPartnerStateCount($conditions);

    public function addPartnerState($partnerState);

	public function updatePartnerState($id, $fields);

	

}