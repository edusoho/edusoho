<?php

namespace Mooc\Service\Testpaper;

interface TestpaperService
{
	public function findUserTestpaperResultsByTestpaperIds(array $testpaperIds,$userId);
}