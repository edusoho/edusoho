<?php

namespace Topxia\Service\Guest\Dao;

interface GuestDao
{
	public function getGuest($id);

	public function findGuestsByIds(array $ids);

    public function searchGuests($conditions, $orderBy, $start, $limit);

    public function searchGuestCount($conditions);

    public function addGuest($guest);

	public function updateGuest($id, $fields);

	

}