<?php
namespace Topxia\Service\SensitiveWord;

interface TokenBucketService
{
	public function getToken($ip);
}