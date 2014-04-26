<?php

namespace Topxia\Service\Guest\Dao;

interface GuestDao
{
	public function getGuest($id);

	public function findGuestByEmail($email);

	public function findGuestByNickname($nickname);

	public function findGuestsByIds(array $ids);

    public function searchGuests($conditions, $orderBy, $start, $limit);

    public function searchGuestCount($conditions);

    public function addGuest($guest);

	public function updateGuest($id, $fields);

	public function waveCounterById($id, $name, $number);

	public function clearCounterById($id, $name);

}