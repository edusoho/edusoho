<?php

namespace Custom\Service\Testpaper\Dao;

interface TestpaperResultDao
{
	public function findUserTestpaperResultsByTestpaperIds(array $testpaperIds,$userId);
}