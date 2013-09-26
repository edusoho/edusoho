<?php

namespace Topxia\Service\Util;

interface MediaParseService
{
	public function parseMediaItem($url, $refresh = false);

	public function parseMediaAlbum($url, $refresh = false);

	public function getMediaByUuid($uuid, $refresh = flase);
}