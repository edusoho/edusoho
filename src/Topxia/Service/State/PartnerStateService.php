<?php
namespace Topxia\Service\State;

interface PartnerStateService
{

	public function getPartnerState($id);

	public function findPartnerStatesByIds(array $ids);

	public function createPartnerState($guestState);

	public function searchPartnerStates($conditions,$sort,$start,$limit);

	public function searchPartnerStateCount($conditions);

	

}