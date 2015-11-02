<?php
namespace Topxia\Service\PostFilter;

interface TokenBucketService
{
	public function getToken($ip, $type);
}