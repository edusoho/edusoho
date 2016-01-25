<?php
namespace Topxia\Service\PostFilter;

interface SensitiveWordService
{
	public function filter($str);
}