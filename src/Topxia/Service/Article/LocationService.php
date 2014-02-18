<?php

namespace Topxia\Service\Artile;

interface LocationService
{
	public function getLocationFullName($id);

	public function getAllLocations();
}