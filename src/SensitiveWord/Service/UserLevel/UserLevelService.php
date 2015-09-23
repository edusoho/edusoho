<?php

namespace SensitiveWord\Service\UserLevel;

interface UserLevelService
{
	public 	function checkPostStatusByLevel();

	public function checkUserStatusByType($type);
	}