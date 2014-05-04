<?php
namespace Topxia\Service\State;

interface GuestStateService
{

	public function getGuestState($id);

	public function findGuestStatesByIds(array $ids);

	public function createGuestState($guestState);

	public function searchGuestStates($conditions,$sort,$start,$limit);

	public function searchGuestStateCount($conditions);

	

}