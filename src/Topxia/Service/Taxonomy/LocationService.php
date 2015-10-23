<?php

namespace Topxia\Service\Taxonomy;

interface LocationService
{
	public function getLocationFullName($id);

	public function getAllLocations();
}