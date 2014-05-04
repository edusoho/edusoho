<?php

namespace Topxia\Service\State\Dao;

interface GuestStateDao
{
	public function getGuestState($id);

	public function findGuestStatesByIds(array $ids);

    public function searchGuestStates($conditions, $orderBy, $start, $limit);

    public function searchGuestStateCount($conditions);

    public function addGuestState($guestState);

	public function updateGuestState($id, $fields);

}