<?php
namespace Topxia\Service\PostFilter;

interface TokenBucketService
{
	public function hasToken($ip, $type);

	public function incrToken($ip, $type);
}