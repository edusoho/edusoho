<?php
namespace Topxia\Service\Guest;

interface GuestService
{

	public function getGuest($id);

	public function getGuestBymTookeen($mTookeen);

	public function findGuestsByIds(array $ids);

	public function createGuest($guest);

	public function searchGuests($conditions,$sort,$start,$limit);

	public function searchGuestCount($conditions);

	

}