<?php
namespace Topxia\Service\State;

interface BusinessStateService
{

	public function getBusinessState($id);

	public function findBusinessStatesByIds(array $ids);

	public function createBusinessState($guestState);

	public function searchBusinessStates($conditions,$sort,$start,$limit);

	public function searchBusinessStateCount($conditions);

	

}